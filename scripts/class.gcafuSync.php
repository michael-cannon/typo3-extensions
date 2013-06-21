<?php

/**
 * This script provides the means of syncronizing Typo3 fe_users tables with
 * ZenCart's zen_address_book, zen_customers, and zen_customers_info.
 *
 * General design notes.
 *
 entries in cannon_bpm.fe_users need to be transferred to
 luican_zc2.zen_address_book, luican_zc2.zen_customers, and
 luican_zc2.zen_customers_info.

 address_books refers to customers with customer_id
 customers refers to address_book with  customers_default_address_id

 I'm thinking, look at fe_users_zen_customers, fuzc, table for the fe_users.uid
 to zen_customers.customers_id relationship.

 * If nothing in that table, select needed zen fields from fe_users given
 * username isn't "visitor_%". 
 ** Cycle through each fe_users result, create customers and address_book
 entries. Then update the same tuples with references to each other. Then create
 barebones zen_customers_info entries

 * When fe_users_zen_customers table has entries
 ** select existing fe_users and find discrepancies, give precedence to entries
 based upon fe_users.tstamp and
 zen_customers_info.customers_info_date_account_last_modified. based upon last
 edit point, t3 or zencart, update the other users table.
 ** select fe_users not in fuzc already and input them as above.
 ** select zen cart folks not in fuzc already and input them in fe_users with
 usergroup 2. match tstamp to help prevent sync issues later on
 *
 * Database Table SQL
	CREATE TABLE `zen_customers_fe_users` (
		`customers_id` int(11) NOT NULL default '0',
		`uid` int(11) NOT NULL default '0',
		KEY `customers_id` (`customers_id`),
		KEY `uid` (`uid`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 *
 * Zencart customer clearance helper
	TRUNCATE TABLE `zen_address_book`;
	TRUNCATE TABLE `zen_customers`;
	TRUNCATE TABLE `zen_customers_info`;
	TRUNCATE TABLE `zen_customers_fe_users`;
 *
 * @author Michael Cannon <michael@peimic.com>
 * @version $Id: class.gcafuSync.php,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $
 */

// it's assumed that zencart database user can connect to typo3 database, if not
// make it so. Usually just add the typo3 table access permissions to the
// zencart database user.

define( 'ZC_DB', DB_DATABASE );

class zcfuSync
{
	// database connection
	var $db						= null;

	var $zcUsers				= array();
	var $t3Users				= array();
	var $t3Pid					= 0;
	var $t3Usergroup			= 0;

	/**
	 * constructor
	 */
	function zcfuSync ()
	{
		// create db object and connect to database
		$this->db				= & ADONewConnection(ADODB_DRIVER);
		$this->db->debug		= ADODB_DEBUG;
		$this->db->PConnect( ADODB_DB_HOST, ADODB_DB_USER, ADODB_DB_PASS
			, ADODB_DB_NAME
		);
	}

	/**
	 * syncronizer
	 */
	function sync ()
	{
		$this->loadUserIds();

		// initial loading
		if ( 0 == count( $this->zcUsers ) && 0 == count( $this->t3Users ) )
		{
			$this->initialUsersLoad();

			return;
		}

		// current updates
		$this->loadUserUpdates();

		// newbies
		$this->loadNewUsers();
	}

	/**
	 * Cross updating of zen and typo3 user tables.
	 */
	function loadUserUpdates ()
	{
		$this->loadFeusersUpdates();

		$this->loadZencartUpdates();
	}

	/**
	 * Look for newer fe_users data and update Zencart with it.
	 *
	 * @return void
	 */
	function loadFeusersUpdates ()
	{
		$fe_users				= T3_DB . '.fe_users';

		// build cross-join table of fe_users, zen_customers, info, and
		// customers_fe_users

		// load updated Typo3 data
		$query					= "
			SELECT
				zcfu.customers_id
				, fu.tstamp
				, fu.company
				, fu.first_name
				, fu.last_name
				, fu.address
				, fu.city
				, fu.zone
				, fu.zip
				, fu.static_info_country
				, fu.date_of_birth
				, fu.email
				, fu.telephone
				, fu.fax
				, fu.password
				, fu.module_sys_dmail_category
				, fu.module_sys_dmail_html
			FROM
				zen_customers_fe_users zcfu
				LEFT JOIN zen_customers_info zci
					ON zcfu.customers_id = zci.customers_info_id
				LEFT JOIN $fe_users fu ON zcfu.uid = fu.uid
			WHERE
				1 = 1
				AND fu.uid IS NOT NULL
				AND zci.customers_info_id IS NOT NULL
				AND fu.tstamp > UNIX_TIMESTAMP(
					zci.customers_info_date_account_last_modified
				) 
		";

		$feUsers				= $this->db->GetAll( $query );

		foreach ( $feUsers as $user )
		{
			$this->updateZencartCustomers( $user );
		}
	}

	/**
	 * Update Zencart customers information with Typo3 fe_users.
	 *
	 * @param array fe_users entry
	 * @return void
	 */
	function updateZencartCustomers ( $user )
	{
		// as you cycle through fe_users update the zen_address_book,
		// zen_customers, and zen_customers_info entries.
		$this->customersUpdate( $user );

		// cross-reference zen_address_book and zen_customers
		$this->customersAddressUpdate( $user );

		$this->customersInfoUpdate( $user );
	}

	/**
	 * Update zen_customers_info from fe_users 
	 *
	 * @param array user information
	 * @return boolean
	 */
	function customersInfoUpdate ( $user )
	{
		$update					= "
			UPDATE zen_customers_info
			SET
				customers_info_date_account_last_modified =
					FROM_UNIXTIME( '{$user[ 'tstamp' ]}' )
			WHERE
				customers_info_id = {$user[ 'customers_id' ]}
		";

		return $this->db->Execute( $update );
	}

	/**
	 * Update default zen_address_book for customers from fe_users
	 *
	 * @param array user information
	 * @return boolean
	 */
	function customersAddressUpdate ( $user )
	{
		$query					= "
			SELECT
				customers_default_address_id
			FROM
				zen_customers
			WHERE
				customers_id = {$user[ 'customers_id' ]}
		";

		$addressId				= $this->db->GetOne( $query );

		if ( ! $addressId )
		{
			return false;
		}

		$countryId				= $this->lookupZenCountryId(
									$user[ 'static_info_country' ]
								);
		$zoneId					= $this->lookupZenZoneId( $countryId
									, $user[ 'zone' ]
								);

		$user					= $this->escapeArray( $user );

		$update					= "
			UPDATE zen_address_book
			SET
				entry_company = '{$user[ 'company' ]}' 
				, entry_firstname = '{$user[ 'first_name' ]}'
				, entry_lastname = '{$user[ 'last_name' ]}'
				, entry_street_address = '{$user[ 'address' ]}' 
				, entry_postcode = '{$user[ 'zip' ]}' 
				, entry_city = '{$user[ 'city' ]}' 
				, entry_state = '{$user[ 'zone' ]}' 
				, entry_country_id = '{$countryId}' 
				, entry_zone_id = '{$zoneId}'

			WHERE
				address_book_id = {$addressId}
		";

		return $this->db->Execute( $update );
	}

	/**
	 * Update fe_users in zen_customers
	 *
	 * @param array user information
	 * @return boolean
	 */
	function customersUpdate( $user )
	{
		$password				= zen_encrypt_password( $user[ 'password' ] );
		$newsletter				= ( $user[ 'module_sys_dmail_category' ] )
									? 1
									: 0;
		$emailFormat			= ( $user[ 'module_sys_dmail_html' ] )
									? 'HTML'
									: 'TEXT';

		$user					= $this->escapeArray( $user );

		$update					= "
			UPDATE zen_customers
			SET
				customers_firstname = '{$user[ 'first_name' ]}'
				, customers_lastname = '{$user[ 'last_name' ]}'
				, customers_dob = '{$user[ 'date_of_birth' ]}'
				, customers_email_address = '{$user[ 'email' ]}'
				, customers_telephone = '{$user[ 'telephone' ]}'
				, customers_fax = '{$user[ 'fax' ]}'
				, customers_password = '{$password}'
				, customers_newsletter = '{$newsletter}'
				, customers_email_format = '{$emailFormat}'
			WHERE
				customers_id = {$user[ 'customers_id' ]}
		";

		return $this->db->Execute( $update );
	}

	/**
	 * Look for newer fe_users data and update Zencart with it.
	 *
	 * @return void
	 */
	function loadZencartUpdates ()
	{
		$fe_users				= T3_DB . '.fe_users';

		// build cross-join table of fe_users, zen_customers, info, and
		// customers_fe_users

		// load updated Zencart  data
		$query					= "
			SELECT 
				fu.uid
				, CONCAT( zc.customers_firstname, ' ', zc.customers_lastname )
					name
				, zc.customers_firstname first_name
				, zc.customers_lastname last_name
				, UNIX_TIMESTAMP( zc.customers_dob ) date_of_birth
				, zc.customers_email_address email
				, zc.customers_nick username
				, zc.customers_telephone telephone
				, zc.customers_fax fax
				/* , zc.customers_password password */
				, CASE
					WHEN ( 'HTML' = zc.customers_email_format ) THEN 1
					ELSE 0
					END module_sys_dmail_html
				, zc.customers_referral referrer_uri
				, zab.entry_company company
				, zab.entry_street_address address
				, zab.entry_postcode zip
				, zab.entry_city city
				, zz.zone_code zone
				, zco.countries_iso_code_3 static_info_country
				, zco.countries_iso_code_3 country
				, UNIX_TIMESTAMP(
					zci.customers_info_date_account_last_modified
				) tstamp

			FROM
				zen_customers_fe_users zcfu
				LEFT JOIN zen_customers zc
					ON zcfu.customers_id = zc.customers_id
				LEFT JOIN $fe_users fu ON zcfu.uid = fu.uid
				LEFT JOIN zen_customers_info zci
					ON zcfu.customers_id = zci.customers_info_id
				LEFT JOIN zen_address_book zab
					ON zc.customers_default_address_id = zab.address_book_id
				LEFT JOIN zen_countries zco
					ON zab.entry_country_id = zco.countries_id
				LEFT JOIN zen_zones zz
					ON zab.entry_country_id = zz.zone_country_id

			WHERE
				1 = 1
				AND zc.customers_id IS NOT NULL
				AND fu.uid IS NOT NULL
				AND zci.customers_info_id IS NOT NULL
				AND zab.address_book_id IS NOT NULL
				AND zco.countries_id IS NOT NULL
				AND zz.zone_id IS NOT NULL
				AND zz.zone_id = zab.entry_zone_id
				/*
				AND UNIX_TIMESTAMP(
					zci.customers_info_date_account_last_modified
				) > fu.tstamp
				*/
		";

		$customers				= $this->db->GetAll( $query );

		foreach ( $customers as $user )
		{
			$this->updateFeusersCustomers( $user );
		}
	}

	/**
	 * Update zen_customers in fe_users 
	 *
	 * @param array customer information
	 * @return boolean
	 */
	function updateFeusersCustomers ( $user )
	{
		$fe_users				= T3_DB . '.fe_users';

		$user					= $this->escapeArray( $user );

		$update					= "
			UPDATE $fe_users
			SET
				name = '{$user[ 'name' ]}'
				, first_name = '{$user[ 'first_name' ]}'
				, last_name = '{$user[ 'last_name' ]}'
				, date_of_birth = '{$user[ 'date_of_birth' ]}'
				, email = '{$user[ 'email' ]}'
				, username = '{$user[ 'username' ]}'
				, telephone = '{$user[ 'telephone' ]}'
				, fax = '{$user[ 'fax' ]}'
				/* , password = '{$user[ 'password' ]}' */
				, module_sys_dmail_html = '{$user[ 'module_sys_dmail_html' ]}'
				, referrer_uri = '{$user[ 'referrer_uri' ]}'
				, company = '{$user[ 'company' ]}'
				, address = '{$user[ 'address' ]}'
				, zip = '{$user[ 'zip' ]}'
				, city = '{$user[ 'city' ]}'
				, zone = '{$user[ 'zone' ]}'
				, static_info_country = '{$user[ 'static_info_country' ]}'
				, country = '{$user[ 'country' ]}'
				, tstamp = '{$user[ 'tstamp' ]}'

			WHERE
				uid = {$user[ 'uid' ]}
		";

		return $this->db->Execute( $update );
	}

	/**
	 * Cross loading of zen and typo3 user tables.
	 *
	 * @param boolean true initial loading
	 * @return void
	 */
	function loadNewUsers ( $initial = false )
	{
		// select new non visitor members from fe_users
		$feUsers				= $this->loadFeusers( $initial );

		foreach ( $feUsers as $user )
		{
			$this->insertFeuserIntoZencart( $user );
		}

		// don't grab folks just loaded from Typo3
		$this->loadUserIds();

		// load new zen folks to typo3
		$customers				= $this->loadCustomers();

		foreach ( $customers as $user )
		{
			$this->insertCustomersIntoFeusers( $user );
		}
	}

	/**
	 * Initial loading of zen and typo3 user tables.
	 */
	function initialUsersLoad ()
	{
		$this->loadNewUsers( true );
	}

	/**
	 * Insert Zen Cart customer into Typo3 fe_users tables.
	 *
	 * @param array customers array
	 * @return void
	 */
	function insertCustomersIntoFeusers ( $user )
	{
		// as you cycle through fe_users create the zen_address_book,
		// zen_customers, and zen_customers_info entries.
		$feuserId				= $this->feusersInsert( $user );

		// create zcfu user entries
		$this->customersFeusersInsert( $user[ 'customers_id' ], $feuserId );
	}

	/**
	 * Insert zen_customers into fe_users 
	 *
	 * @param array customer information
	 * @return integer
	 */
	function feusersInsert( $user )
	{
		$fe_users				= T3_DB . '.fe_users';

		$user					= $this->escapeArray( $user );

		$insert					= "
			INSERT INTO $fe_users
			SET
				usergroup = '{$user[ 'usergroup' ]}'
				, pid = '{$user[ 'pid' ]}'
				, name = '{$user[ 'name' ]}'
				, first_name = '{$user[ 'first_name' ]}'
				, last_name = '{$user[ 'last_name' ]}'
				, date_of_birth = '{$user[ 'date_of_birth' ]}'
				, email = '{$user[ 'email' ]}'
				, username = '{$user[ 'username' ]}'
				, telephone = '{$user[ 'telephone' ]}'
				, fax = '{$user[ 'fax' ]}'
				/* , password = '{$user[ 'password' ]}' */
				, module_sys_dmail_html = '{$user[ 'module_sys_dmail_html' ]}'
				, referrer_uri = '{$user[ 'referrer_uri' ]}'
				, company = '{$user[ 'company' ]}'
				, address = '{$user[ 'address' ]}'
				, zip = '{$user[ 'zip' ]}'
				, city = '{$user[ 'city' ]}'
				, zone = '{$user[ 'zone' ]}'
				, static_info_country = '{$user[ 'static_info_country' ]}'
				, country = '{$user[ 'country' ]}'
				, crdate = '{$user[ 'crdate' ]}'
				, tstamp = '{$user[ 'tstamp' ]}'
				, internal_note = '{$user[ 'internal_note' ]}'
		";

		$this->db->Execute( $insert );

		return $this->db->insert_ID();
	}

	/**
	 * Return array containing Zen cart customers information.
	 *
	 * @param boolean initial
	 * @param boolean exclude/include
	 */
	function loadCustomers ( $initial = false, $exclude = true )
	{
		$customers				= array();
		$customerUids			= '';

		if ( ! $initial && 0 < count( $this->zcUsers ) )
		{
			$not				= ( $exclude )
									? 'NOT'
									: '';

			$customerUids			= " AND zc.customers_id $not IN ( "
									. implode( ', ', $this->zcUsers )
									. ' )';
		}

		$query					= "
			SELECT 
				zc.customers_id 
				, {$this->t3Usergroup} as usergroup
				, {$this->t3Pid} as pid
				, CONCAT( zc.customers_firstname, ' ', zc.customers_lastname )
					name
				, zc.customers_firstname first_name
				, zc.customers_lastname last_name
				, UNIX_TIMESTAMP( zc.customers_dob ) date_of_birth
				, zc.customers_email_address email
				, zc.customers_nick username
				, zc.customers_telephone telephone
				, zc.customers_fax fax
				/* , zc.customers_password password */
				, CASE
					WHEN ( 'HTML' = zc.customers_email_format ) THEN 1
					ELSE 0
					END module_sys_dmail_html
				, zc.customers_referral referrer_uri
				, zab.entry_company company
				, zab.entry_street_address address
				, zab.entry_postcode zip
				, zab.entry_city city
				, zz.zone_code zone
				, zco.countries_iso_code_3 static_info_country
				, zco.countries_iso_code_3 country

				, UNIX_TIMESTAMP( zci.customers_info_date_account_created )
					crdate
				, UNIX_TIMESTAMP(
					zci.customers_info_date_account_last_modified
				) tstamp
				, CONCAT( 'zen_customers.customers_id = ', zc.customers_id )
					internal_note

			FROM zen_customers zc
				LEFT JOIN zen_address_book zab
					ON zc.customers_default_address_id = zab.address_book_id
				LEFT JOIN zen_customers_info zci
					ON zc.customers_id = zci.customers_info_id
				LEFT JOIN zen_countries zco
					ON zab.entry_country_id = zco.countries_id
				LEFT JOIN zen_zones zz
					ON zab.entry_country_id = zz.zone_country_id

			WHERE
				1 = 1
				AND zab.address_book_id IS NOT NULL
				AND zci.customers_info_id IS NOT NULL
				AND zab.entry_country_id IS NOT NULL
				AND zco.countries_id IS NOT NULL
				AND zz.zone_id IS NOT NULL
				AND zz.zone_id = zab.entry_zone_id
				$customerUids
		";

		$results				= $this->db->Execute( $query );

		while ( ! $results->EOF )
		{
			$customers[]			= $results->fields;
			$results->MoveNext();
		}

		return $customers;
	}

	/**
	 * Insert Typo3 fe_users into Zen Cart customer tables.
	 *
	 * @param array fe_users array
	 * @return void
	 */
	function insertFeuserIntoZencart ( $user )
	{
		// as you cycle through fe_users create the zen_address_book,
		// zen_customers, and zen_customers_info entries.
		$customersInsertId		= $this->customersInsert( $user );
		$addressInsertId		= $this->addressInsert( $customersInsertId
									, $user
								);

		// cross-reference zen_address_book and zen_customers
		$this->customersDefaultAddressUpdate( $customersInsertId
			, $addressInsertId
		);

		$this->customersInfoInsert( $customersInsertId, $user );

		// create zcfu user entries
		$this->customersFeusersInsert( $customersInsertId, $user[ 'uid' ] );
	}

	/**
	 * Insert customers_id and fe_users.uid into zen_customers_fe_users
	 *
	 * @param integer zen cart customer id
	 * @param integer fe_user uid
	 * @return void
	 */
	function customersFeusersInsert ( $customerId, $userId )
	{
		$insert					= "
			INSERT INTO zen_customers_fe_users
			SET
				customers_id = {$customerId}
				, uid = {$userId}
		";

		$this->db->Execute( $insert );
	}

	/**
	 * Update zen_customers.default_address_book_id
	 *
	 * @param integer customer id
	 * @param integer address id
	 */
	function customersDefaultAddressUpdate( $customerId, $addressId )
	{
		$query					= "
			UPDATE zen_customers
			SET customers_default_address_id = $addressId
			WHERE customers_id = $customerId
		";

		$result					= $this->db->Execute( $query );
	}

	/**
	 * Insert fe_users into zen_customers_info
	 *
	 * @param integer customer id
	 * @param array user information
	 * @return void
	 */
	function customersInfoInsert ( $customerId, $user )
	{
		$insert					= "
			INSERT INTO zen_customers_info
			SET
				customers_info_id = {$customerId}
				, customers_info_date_account_created =
					FROM_UNIXTIME( '{$user[ 'crdate' ]}' )
				, customers_info_date_account_last_modified =
					FROM_UNIXTIME( '{$user[ 'tstamp' ]}' )
		";

		$this->db->Execute( $insert );
	}

	/**
	 * Insert fe_users into zen_address_book
	 *
	 * @param integer customer id
	 * @param array user information
	 * @return integer
	 */
	function addressInsert ( $customerId, $user )
	{
		$countryId				= $this->lookupZenCountryId(
									$user[ 'static_info_country' ]
								);
		$zoneId					= $this->lookupZenZoneId( $countryId
									, $user[ 'zone' ]
								);

		$user					= $this->escapeArray( $user );

		$insert					= "
			INSERT INTO zen_address_book
			SET
				customers_id = {$customerId}
				, entry_company = '{$user[ 'company' ]}' 
				, entry_firstname = '{$user[ 'first_name' ]}'
				, entry_lastname = '{$user[ 'last_name' ]}'
				, entry_street_address = '{$user[ 'address' ]}' 
				, entry_postcode = '{$user[ 'zip' ]}' 
				, entry_city = '{$user[ 'city' ]}' 
				, entry_state = '{$user[ 'zone' ]}' 
				, entry_country_id = '{$countryId}' 
				, entry_zone_id = '{$zoneId}'
		";

		$this->db->Execute( $insert );

		return $this->db->insert_ID();
	}

	/**
	 * Returns integer of zone/state/province id
	 *
	 * @param integer country id
	 * @param string user zone/state/province
	 * @return integer
	 */
	function lookupZenZoneId ( $countryId, $zone )
	{
		// default to USA
		$zoneId					= 0;

		$query					= "
			SELECT
				zone_id
			FROM zen_zones
			WHERE
				1 = 1
				AND zone_country_id = $countryId
				AND (
					zone_code = '$zone'
					OR zone_name = '$zone'
				)
		";

		$results				= $this->db->Execute( $query );

		return ( ! $results->EOF && $results->fields[ 'zone_id' ] )
			? $results->fields[ 'zone_id' ]
			: $zoneId;
	}

	/**
	 * Returns integer of country id
	 *
	 * @param string user country
	 * @return integer
	 */
	function lookupZenCountryId ( $country )
	{
		// default to USA
		$countryId				= 223;

		$query					= "
			SELECT
				countries_id
			FROM zen_countries
			WHERE
				1 = 1
				AND (
					countries_iso_code_2 = '$country'
					OR countries_iso_code_3 = '$country'
				)
		";

		$results				= $this->db->Execute( $query );

		return ( ! $results->EOF && $results->fields[ 'countries_id' ] )
			? $results->fields[ 'countries_id' ]
			: $countryId;
	}

	/**
	 * Insert fe_users into zen_customers
	 *
	 * @param array user information
	 * @return integer
	 */
	function customersInsert( $user )
	{
		$password				= zen_encrypt_password( $user[ 'password' ] );
		$newsletter				= ( $user[ 'module_sys_dmail_category' ] )
									? 1
									: 0;
		$emailFormat			= ( $user[ 'module_sys_dmail_html' ] )
									? 'HTML'
									: 'TEXT';

		$user					= $this->escapeArray( $user );

		$insert					= "
			INSERT INTO zen_customers
			SET
				customers_firstname = '{$user[ 'first_name' ]}'
				, customers_lastname = '{$user[ 'last_name' ]}'
				, customers_dob = '{$user[ 'date_of_birth' ]}'
				, customers_email_address = '{$user[ 'email' ]}'
				, customers_nick = '{$user[ 'username' ]}'
				, customers_telephone = '{$user[ 'telephone' ]}'
				, customers_fax = '{$user[ 'fax' ]}'
				, customers_password = '{$password}'
				, customers_newsletter = '{$newsletter}'
				, customers_email_format = '{$emailFormat}'
		";


		$this->db->Execute( $insert );

		return $this->db->insert_ID();
	}

	/**
	 * Return array containing feusers information.
	 *
	 * @param boolean initial
	 * @param boolean exclude/include
	 */
	function loadFeusers ( $initial = false, $exclude = true )
	{
		$feusers				= array();
		$feuserUids				= '';
		$fe_users				= T3_DB . '.fe_users';

		if ( ! $initial && 0 < count( $this->t3Users ) )
		{
			$not				= ( $exclude )
									? 'NOT'
									: '';

			$feuserUids			= " AND uid $not IN ( "
									. implode( ', ', $this->t3Users )
									. ' )';
		}

		$query					= "
			SELECT 
				uid
				, crdate
				, tstamp
				, company
				, first_name
				, last_name
				, address
				, city
				, zone
				, zip
				, static_info_country
				, date_of_birth
				, email
				, telephone
				, fax
				, username
				, password
				, module_sys_dmail_category
				, module_sys_dmail_html

			FROM $fe_users

			WHERE
				1 = 1
				AND NOT disable
				AND NOT deleted
				AND username NOT LIKE 'visitor_%'
				$feuserUids
		";

		$results				= $this->db->Execute( $query );

		while ( ! $results->EOF )
		{
			$feusers[]			= $results->fields;
			$results->MoveNext();
		}

		return $feusers;
	}

	/**
	 * Load user id arrays.
	 */
	function loadUserIds ()
	{
		$ids					= array();

		$query					= '
			SELECT *
			FROM zen_customers_fe_users
		';

		$result					= $this->db->Execute( $query );

		while( ! $result->EOF )
		{
			$this->zcUsers[]	= $result->fields[ 'customers_id' ];
			$this->t3Users[]	= $result->fields[ 'uid' ];
			$result->MoveNext();
		}
	}

	/**
	 * Returns array contains MySQL escaped values.
	 *
	 * @param array
	 * @return array
	 */
	function escapeArray ( $array )
	{
		foreach ( $array as $key => $item )
		{
			$array[ $key ]		= mysql_real_escape_string( $item );
		}

		return $array;
	}
}

?>
