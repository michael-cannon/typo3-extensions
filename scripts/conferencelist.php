<?php

/**
 * Conference access list downloader
 *
 * @author Michael Cannon, michael@peimic.com
 * @version $Id: conferencelist.php,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $
 */

// grab database login from Typo3 configuration file
// $typo_db_username
// $typo_db_password
// $typo_db_host
// $typo_db
include_once( '/home/bpm/public_html/bpminstitute/typo3conf/localconf.php' );

// download helper
require_once( CB_COGS_DIR . 'cb_html.php' );

// create db conection
$db								= mysql_connect( $typo_db_host
									, $typo_db_username
									, $typo_db_password
								)
								or die( 'Could not connect to database' );

// select database
mysql_select_db( $typo_db )
	or die( 'Could not select database' );

// load fe_users folder locations
$userPid						= 20;

$sql							= "
	SELECT 
	u.uid member_id
	/*
		, FROM_UNIXTIME( u.crdate, '%M %e, %Y %l:%i %p' ) signup_date
		, FROM_UNIXTIME( u.tstamp, '%M %e, %Y %l:%i %p' ) modified_date
		*/
		, u.first_name
		, u.last_name
		, u.email
		/*
		, u.username
		, u.title
		, u.company
		, u.address
		, u.city
		, u.zone
		, u.zip
		, c.cn_short_en country
		*/
		, u.telephone
		/*
		, u.fax
		, u.www
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter1 = 1 ) THEN 'Yes'
			ELSE 'No'
			END The_BPM_Bulletin
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter5 = 1 ) THEN 'Yes'
			ELSE 'No'
			END SOA_Newsletter
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter2 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Business_Rules_Newsletter
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter3 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Operational_Performance_Newsletter
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter4 = 1 ) THEN 'Yes'
			ELSE 'No'
			END RFID_Newsletter
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter6 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Governance_Newsletter
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter7 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Enterprise_Architecture
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter8 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Compliance
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter9 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Government
		, CASE
			WHEN ( u.module_sys_dmail_html = 1 ) THEN 'Yes'
			ELSE 'No'
			END newsletter_html
		, CASE
			WHEN ( FIND_IN_SET( '1', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Complimentary_Member
		, CASE
			WHEN ( FIND_IN_SET( '2', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Professional_Member
		, CASE
			WHEN ( FIND_IN_SET( '16', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Corporate_Member
		, CASE
			WHEN ( FIND_IN_SET( '4', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Professional_Guest_Member
		, CASE
			WHEN ( FIND_IN_SET( '5', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Visitor_White_Paper
		, CASE
			WHEN ( FIND_IN_SET( '6', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Visitor_Round_Table
		, CASE
			WHEN ( FIND_IN_SET( '7', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Vistior_Presentation
		, CASE
			WHEN ( FIND_IN_SET( '9', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Conference_Chicago
		, CASE
			WHEN ( FIND_IN_SET( '10', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Conference_SF
		, CASE
			WHEN ( FIND_IN_SET( '11', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Conference_DC
		, CASE
			WHEN ( FIND_IN_SET( '12', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Conference_NY
		, CASE
			WHEN ( FIND_IN_SET( '19', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Requested_Conference_Access
		, CASE
			WHEN ( FIND_IN_SET( '18', u.usergroup ) ) THEN 'X'
			ELSE ''
			END BPM_Site_Member
		, CASE
			WHEN ( FIND_IN_SET( '17', u.usergroup ) ) THEN 'X'
			ELSE ''
			END SOA_Site_Member
		, u.referrer_uri referrer_url

		, CASE
			WHEN ( 0 < u.starttime )
				THEN FROM_UNIXTIME( u.starttime, '%M %e, %Y' )
				ELSE ''
			END start_date

		, CASE
			WHEN ( 0 < u.endtime )
				THEN FROM_UNIXTIME( u.endtime, '%M %e, %Y' )
				ELSE ''
			END end_date

		, u.internal_note

		, CASE
			WHEN ( u.processed = 1 ) THEN 'Yes'
			ELSE 'No'
			END processed
		, CASE
			WHEN ( u.paid = 1 ) THEN 'Yes'
			ELSE 'No'
			END paid

		, CASE
			WHEN ( u.disable  = 1 ) THEN 'Yes'
			ELSE 'No'
			END disabled

		, CASE
			WHEN ( u.join_agree = 1 ) THEN 'Yes'
			ELSE 'No'
			END Join_Agree

		, CASE
			WHEN ( 0 < u.lastlogin )
				THEN FROM_UNIXTIME( u.lastlogin, '%M %e, %Y' )
				ELSE ''
			END last_login

		, CASE
			WHEN ( u.tx_memberexpiry_expired = 1 ) THEN 'Yes'
			ELSE 'No'
			END membership_expired

		, CASE
			WHEN ( 0 < u.tx_memberexpiry_expiretime )
				THEN FROM_UNIXTIME( u.tx_memberexpiry_expiretime, '%M %e, %Y' )
				ELSE ''
			END membership_expired_date

		, CASE
			WHEN ( 0 < u.tx_memberexpiry_emailsenttime )
				THEN FROM_UNIXTIME( u.tx_memberexpiry_emailsenttime, '%M %e, %Y' )
				ELSE ''
			END membership_expiry_email_date
		*/

	FROM fe_users u
	/*
		LEFT JOIN static_countries c ON u.static_info_country = c.cn_iso_3
	*/
	WHERE 1 = 1
		AND u.pid = $userPid
		and find_in_set(9, usergroup)
		and email not in (
			'agendre@ilog.com'
			, 'ajtravis@mindspring.com'
			, 'alberta.a.oney@jpmchase.com'
			, 'amanes@burtongroup.com'
			, 'andrew@spanyi.com'
			, 'andy.dailey@haworth.com'
			, 'angu3@allstate.com'
			, 'anita.jeyakumar@chetanaa.com'
			, 'aperq@allstate.com'
			, 'aquraishi@pershing.com'
			, 'ARamias@ThePDLab.com'
			, 'arbo@qualiware.com'
			, 'ascrima@ipmcinc.com'
			, 'ashish@adobe.com'
			, 'atam@adobe.com '
			, 'atortolero@sungardfutures.com'
			, 'badar@i3-t.com'
			, 'baobrien@deloitte.com'
			, 'barbara.swanson@global360.com'
			, 'bchampli@allstate.com'
			, 'bczar@allstate.com'
			, 'bdost@allstate.com'
			, 'ben.cody@global360.com'
			, 'benjamin_choi@baxter.com'
			, 'beth.a.raffety@sbc.com'
			, 'betsy.capello@usbank.com'
			, 'bhaskar.chakrabarti@jpmchase.com'
			, 'bhatti@plexobject.com'
			, 'bhowell2@ford.com'
			, 'bkappe@pathf.com'
			, 'bkraj@allstate.com'
			, 'bolson@castironsys.com'
			, 'bordelonci@aol.com'
			, 'boris.lublinsky@cna.com'
			, 'brian.basiliere@capeclear.com'
			, 'brittneya@brandtinfo.com'
			, 'brsilver@earthlink.net'
			, 'bsaba@allstate.com'
			, 'burleyk@microsoft.com'
			, 'bvonhalle@kpiusa.com'
			, 'carol.fletcher@gmacrfc.com'
			, 'carol.thor@acxiom.com'
			, 'carolyn_podgorski@merck.com'
			, 'caspar.hunsche@pcor.com'
			, 'charlie.glick@webmethods.com'
			, 'chris@velocitygroupinc.com'
			, 'Christine.Button@Wachovia.com'
			, 'cliff.phillips@uug.com'
			, 'colleen.nichols@global360.com'
			, 'communications@pmi-chicagoland.org'
			, 'connie.anast@gt.com'
			, 'cseveryns@savvion.com'
			, 'cververs@thoughtworks.com'
			, 'dan@valuecreationpartners.com'
			, 'daniel.garrity@sun.com'
			, 'dave.bennett@usbank.com'
			, 'dave.irish@pega.com'
			, 'David.bowers@Pega.com'
			, 'david.epstein@zurichna.com'
			, 'David.Heidt@enterprise-agility.com'
			, 'daviestg@ldschurch.org'
			, 'dchaput@lambert-tech.com'
			, 'debra.boykin@molsoncoors.com'
			, 'deepak.singh@attglobal.net'
			, 'deonnewm@us.ibm.com'
			, 'dgifford@infoworksinc.net'
			, 'dharmendra.balwani@united.com'
			, 'dheldt2002@yahoo.com'
			, 'dianewargo@discoverfinancial.com'
			, 'dkardash@azophi.com'
			, 'dKastning@sun.com'
			, 'DKibelkis@griffithlaboratories.com'
			, 'dlancour@wbmi.com'
			, 'donalsonjd@ldschurch.org'
			, 'Donna.Quick-Keckler.ctr@disa.mil'
			, 'doug.kennedy.aly5@statefarm.com'
			, 'drenz@tpna.com'
			, 'dritter@proformacorp.com'
			, 'dtrue@allstate.com'
			, 'EastleyLV@ldschurch.org'
			, 'ed.vail@telelogic.com'
			, 'ekamsteeg@cordsy.com'
			, 'elproct@nsa.gov'
			, 'emaca@allstate.com'
			, 'emcmahon@burtongroup.com'
			, 'encorebz@us.ibm.com'
			, 'enooteboom@cordys.com'
			, 'eric.deitert@pega.com'
			, 'eric.goodheart@capeclear.com'
			, 'eric_rustomji@baxter.com'
			, 'eschultz@kahg.com'
			, 'finance@abpmp.org'
			, 'fmartinez@bluetitan.com'
			, 'fsnyder@lawsonproducts.com'
			, 'gaccove@allstate.com'
			, 'gary.degregorio@motorola.com'
			, 'gary.so@webmethods.com'
			, 'gehringjg@ldschurch.org'
			, 'general.grant@gm.com'
			, 'george.w.brown@intel.com'
			, 'gg2147@sbc.com'
			, 'gil.castellanos@bp.com'
			, 'gkunkel@amfam.com'
			, 'gmalone@oneteamtech.com'
			, 'gmiller@allstate.com'
			, 'greg.skluzacek@motorola.com'
			, 'grummler@performancedesignlab.com'
			, 'gsmith@kanbay.com'
			, 'gvicotoria@proformacorp.com'
			, 'hans.bjork@skf.com'
			, 'heathjh@ldschurch.org'
			, 'hgrether@allstate.com'
			, 'honeill@adobe.com'
			, 'hrietveld@cordys.com'
			, 'iryab@allstate.com'
			, 'james@qualiware.com'
			, 'jamestaylor@fairisaac.com'
			, 'janderson@lombardisoftware.com'
			, 'Janice.Hill@Daugherty.com'
			, 'jay.yusko@infores.com'
			, 'jblock@uwsa.edu'
			, 'jbloomberg@zapthink.com'
			, 'jbonds@castironsys.com'
			, 'jdebrocke@cordys.com'
			, 'jdivenere@zebra.com'
			, 'jeff.sturrock@global360.com'
			, 'jengesser@savvion.com'
			, 'jennifer.holmes@nav-international.com'
			, 'Jennifer.Steele@sun.com'
			, 'jfreedlund@agiletek.com '
			, 'jhamilton@forsyth.com'
			, 'jhartwig@allstate.com'
			, 'jhei8@allstate.com'
			, 'jhutchen@cordys.com'
			, 'jim.colson@proformacorp.com'
			, 'jim.dayton@capitaloneauto.com'
			, 'jim.owen@lombardisoftware.com'
			, 'jim.rudden@lombardisoftware.com'
			, 'jim_klempner@hcsc.net'
			, 'jmcglynn@webMethods.com'
			, 'jmillard@bluetitan.com'
			, 'jmmiller@fedins.com'
			, 'jmuniz@centrichf.com'
			, 'jnelq@allstate.com'
			, 'joakim.bergelin@acandofrontec.se'
			, 'jodi.eberhardt@gmacrfc.com'
			, 'joelstreightiff@fairisaac.com'
			, 'joevanbelkum@fairisaac.com'
			, 'john.karnblad@skf.com'
			, 'john.sarich@patni.com'
			, 'john.wolsborn@bearingpoint.com'
			, 'johnfiorella@lawsonproducts.com'
			, 'jonah.ellin@infores.com'
			, 'joseph.castle@gsa.gov'
			, 'Joseph.francis@pcor.com'
			, 'Joseph.McWhirter@sun.com'
			, 'Joseph_A_Piccolo@Progressive.com'
			, 'jpalmqui@comcast.net'
			, 'jpietsch@amfam.com'
			, 'jprice@edc.ca'
			, 'jreis@wbmi.com'
			, 'Jrhilty@aol.com'
			, 'jriley@wbmi.com'
			, 'jrojas1@allstate.com'
			, 'jsheth@cordys.com'
			, 'jshinn@griffithlaboratories.com'
			, 'jskelton@caseworks.ca'
			, 'jwatson@doculabls.com'
			, 'jwitt@forsythe.com'
			, 'karen.kaye@faa.gov'
			, 'karl_b_rosenhan@dom.com'
			, 'ken.hack@usbank.com'
			, 'kenorr@kenorrinst.com'
			, 'kevin.odonovan@telelogic.com'
			, 'kimberly.bryant@suntrust.com'
			, 'kimberly.hammer@dstawd.com'
			, 'klinb@allstate.com'
			, 'kpitzer@centrichf.com'
			, 'kreeves@us.ibm.com'
			, 'kris.heinzen@gmacrfc.com'
			, 'kristen.daily@appian.com'
			, 'ksc2j@allstate.com'
			, 'ksc2w@allstate.com'
			, 'kstape@tpna.com'
			, 'kurt.wickhorst@usbank.com'
			, 'kvollmer@forrester.com'
			, 'kyle.chaffer@sun.com'
			, 'lance.hill@webmethods.com'
			, 'larry.wallendorff@telelogic.com'
			, 'larrygr@mtcorp.com'
			, 'lasdivens@bpa.gov'
			, 'lbennett@agiletek.com'
			, 'lee.garver@zurichna.com'
			, 'Lenora.Thompson@suntrust.com'
			, 'LeslieD@mclabs.comgrant'
			, 'Lgfeller@bcbsal.org'
			, 'lgoldberg@kpiusa.com'
			, 'linthicum@att.net'
			, 'llaya@allstate.com'
			, 'llharris@bpa.gov'
			, 'lmcmanis@kraft.com'
			, 'lmeyer@ipmcinc.com'
			, 'lmurray@oneteamtech.com'
			, 'lorie.sather@disa.mil'
			, 'lpellegrino@proformacorp.com'
			, 'lsullivan@ilog.com'
			, 'lwi2x@allstate.com'
			, 'lzhukov@bte-inc.com'
			, 'mabraham@comcast.net'
			, 'marglee@deloitte.ca'
			, 'maria@savvion.com'
			, 'Marina.Lempert@united.com'
			, 'mark.johnson@global360.com'
			, 'mark.oconnor@gmacrfc.com'
			, 'Mark.Peterson@CoeurGroup.com'
			, 'mark.ryan@pega.com'
			, 'marklayden@fairisaac.com'
			, 'marom@allstate.com'
			, 'martin@marchpr.com'
			, 'marvin.schoch@pega.com'
			, 'mary.prudden@speakeasy.net'
			, 'matt.dussling@lombardisoftware.com'
			, 'matthew.parsons@appiancorp.com'
			, 'mblbb@allstate.com'
			, 'mbu24@allstate.com'
			, 'mcavanaugh@ilog.com'
			, 'mdoli@allstate.com'
			, 'michael.beckley@appiancorp.com'
			, 'michael.hittesdorf@cna.com'
			, 'Michael.manion@pega.com'
			, 'michael@velocitygroupinc.com'
			, 'michelle.mcclinton@bankofamerica.com'
			, 'Michelle.Silanskis@gt.com'
			, 'mike.kaminski@proformacorp.com'
			, 'mike.rosen@azoratech.comjkarnblad'
			, 'mike@colosa.com'
			, 'mmcleod@ipmcinc.com'
			, 'mollymcdermott@fairisaac.com'
			, 'morgenthaljp@avorcor.com'
			, 'mphinick@castironsys.com'
			, 'mplacek@castironsys.com'
			, 'mreddy@bpop.com'
			, 'msolt@allstate.com'
			, 'mwkoscielny@aaamichigan.com'
			, 'nabay@allstate.com'
			, 'nalbarracin@objectivearchitects.com'
			, 'nancy.twombly@pega.com'
			, 'nancy_bilodeau@yahoo.ca'
			, 'nbraam@gcmlp.com'
			, 'nkress@uchicago.edu'
			, 'npath@allstate.com'
			, 'nshah@bcbsal.org'
			, 'orazavi@cordys.com'
			, 'orodriguez@innoveer.com'
			, 'owen.jones@lifeway.com'
			, 'padmaraj@siddts.com'
			, 'pam.dunn@global360.com'
			, 'pamesa@bpa.gov'
			, 'pat@cam-i.org'
			, 'patrick.leblanc@astrazeneca.com'
			, 'peter.fong@jpmchase.com'
			, 'peter.gilbertson@cunamutual.com'
			, 'pfjelsta@thepdlab.com'
			, 'pgrant@bcbsal.org'
			, 'phillip_l_rich@keane.com'
			, 'phutchen@cordys.com'
			, 'pkanyadi@cordys.com'
			, 'pkurth@amfam.com'
			, 'pmorrissey@savvion.com'
			, 'poconnor@e-brilliance.com'
			, 'pponnachath@castironsys.com'
			, 'r.aiello-gertz@na.modine.com'
			, 'ralphwhittle@earthlink.net'
			, 'ras@qualiware.com'
			, 'rbrando@edc.ca'
			, 'rbryant@savvion.com'
			, 'rdove@wbmi.com'
			, 'reginadeg@optonline.net'
			, 'reiaperkins@discoverfinancial.com'
			, 'rgiangiulio@e-brilliance.com'
			, 'RHawk@WBMI.Com'
			, 'rhea.coleman@fnf.com'
			, 'rhlubbers@aep.com'
			, 'rholterman@cordys.com'
			, 'richard.bridges@telus.com'
			, 'rkoka@seec.com'
			, 'rmeye3@amfam.com'
			, 'robert.scheer@grainger.com'
			, 'rocio@qualiware.com'
			, 'ron.sebor@zurichna.com'
			, 'Ross.Altman@sun.com'
			, 'rpellegrino@proformacorp.com'
			, 'rphiliotis@yahoo.com'
			, 'rryan@pgtindustries.com'
			, 'rwin4@allstate.com'
			, 'RZaporski@WBMI.com'
			, 's.nicoll@banklife.com'
			, 'SBethel@Ameren.com'
			, 'scott.rogge@physiciansmutual.com'
			, 'sean.ghassemi@jpmchase.com'
			, 'setrag.khoshafian@pega.com'
			, 'sgorc@allstate.com'
			, 'shannon.molander.jioo@statefarm.com'
			, 'shassan-ali@central-bank.org.tt'
			, 'showcase@showcasetechnologies.com'
			, 'sidharth.nazareth@appiancorp.com'
			, 'sjacobs@amfam.com'
			, 'smavuri@ruralins.com'
			, 'soumit.nandi@united.com'
			, 'spopper@ipmcinc.com'
			, 'sridhar.koneru@brunswick.com'
			, 'srotter@adobe.com'
			, 'sseetharamachar@aaamichigan.com'
			, 'stacee.wolfe@zcsterling.com'
			, 'StacyTaylor@bcbsal.org'
			, 'steve.littig@softwareagusa.com'
			, 'steve.rosso@lombardisoftware.com'
			, 'steve.thorne@abnamro.com'
			, 'steve.vegter@haworth.com'
			, 'stu.hammer@eds.com'
			, 'stuart.schwartz@fieldcontainer.com'
			, 'summer.amin@appiancorp.com'
			, 'susan_hanellin@merck.com'
			, 'sushil.paigankar@patni.com'
			, 'swilk@us.ibm.com'
			, 'tadams@chaosity.com'
			, 'tdwyer@yankeegroup.com'
			, 'ted.brumm@pega.com'
			, 'tgreen8@allstate.com'
			, 'thehr@allstate.com'
			, 'tim@palmzone.net'
			, 'timothy.dempsey@gsa.gov'
			, 'tmichalak@webMethods.com'
			, 'tohara@webMethods.com'
			, 'tony.pasma@global360.com'
			, 'tpatton@cordys.com'
			, 'treat@aol.com'
			, 'tshannon@bcbsal.org'
			, 'tventrel@allstate.com'
			, 'twooa@allstate.com'
			, 'tztaylo@transunion.com'
			, 'valeska@uvium.com'
			, 'vmcki@allstate.com'
			, 'wade.sorenson@gmacrfc.com'
			, 'walter.jankowski@cunamutual.com'
			, 'wes.horeni@nav-international.com'
			, 'William.Roth@da.state.ks.us'
			, 'WMUlrich@compuserve.com'
		)
		AND u.deleted = 0
		AND u.disable = 0
	/*
	ORDER BY u.uid DESC
	LIMIT 100
	*/
";

// cbDebug( 'sql', $sql );
// exit();

// get query result
$result							= mysql_query( $sql )
									or die( 'Query failed: ' . mysql_error() );

// file to write to
$filenameTmp					= '/tmp/' . uniqid() . '.csv';
$filelink						= fopen( $filenameTmp, 'w+' );

if ( $result && $data = mysql_fetch_assoc( $result ) )
{
	$bpmInvolvement			= 'involvement';
	$bpmInvolvementId		= 18;
	$soaInvolvement			= 'involvement-soa';
	$soaInvolvementId		= 17;

	$involvement			= loadInvolvement();
	// cbDebug( 'involvement', $involvement );

	// create headings
	$membersHeader			= array_keys( $data );
	$membersHeader[]		= $bpmInvolvement;
	$membersHeader[]		= $soaInvolvement;
	$membersCsv				= cbMkCsvString( $membersHeader );

	fwrite( $filelink, $membersCsv );

	// cycle through formmail items
	do
	{
		// MLC line breaks make for ugly downloads
		foreach ( $data as $key => $value )
		{
			if ( preg_match( "#\s#", $value ) )
			{
				$data[ $key ] 	= preg_replace( "#\s#"
									, ' '
									, $value
								);
			}

			$data[ $bpmInvolvement ]	= isset( $involvement[ $data[ 'member_id' ] ][ $bpmInvolvementId ] )
											? $involvement[ $data[ 'member_id' ] ][ $bpmInvolvementId ]
											: '';
			$data[ $soaInvolvement ]	= isset( $involvement[ $data[ 'member_id' ] ][ $soaInvolvementId ] )
											? $involvement[ $data[ 'member_id' ] ][ $soaInvolvementId ]
											: '';
		}

		// push member onto members file
		$membersCsv				= cbMkCsvString( $data );
		fwrite( $filelink, $membersCsv );
	} while ( $data = mysql_fetch_assoc( $result ) );

	// free up our result set
	mysql_free_result( $result );
}

// close db connection
mysql_close( $db );

// display 
// make filename with today's date and memberlist
$today							= cbSqlNow( true );
$filename						= 'conference_list_' . $today . '.csv';

// read newly created file back for sending to user
$membersCsv						= file_get_contents( $filenameTmp );

fclose( $filelink );
unlink( $filenameTmp );

// download members list
cbBrowserDownload( $filename, $membersCsv );

/**
 * Loads involvement from survey results into array.
 *
 * @return array
 */
function loadInvolvement()
{
	$bpmInvolvement			= 'involvement';
	$soaInvolvement			= 'involvement-soa';

	$array						= array();

	$sql						= "
		SELECT fe_cruser_id
			, results
			, domain_group_id
		FROM tx_mssurvey_results
		WHERE 1 = 1
			AND deleted = 0
		/*
		ORDER BY fe_cruser_id DESC
		LIMIT 100
		*/
	";

	$result						= mysql_query( $sql )
									or die( 'Query failed: ' . mysql_error() );

	if ( $result && $data = mysql_fetch_assoc( $result ) )
	{
		do
		{
			$dr					= explode( '","', trim($data['results'], '"') );
			$results			= '';
			
			foreach ( $dr as $item )
			{
				list( $itemName, $answer )	= explode( '":"', $item );

				if ( preg_match( "#^($bpmInvolvement|$soaInvolvement)$#"
						, $itemName
					)
				)
				{
					$results	=  stripslashes( 
										preg_replace( "#\s#"
											, ' '
											, $answer )
									);

					// only need short item
					list( $results )	= explode( ':', $results );
				}
			}

			$array[ $data[ 'fe_cruser_id' ] ][ $data[ 'domain_group_id' ] ] =
									$results;
		} while ( $data = mysql_fetch_assoc( $result ) );

		// free up our result set
		mysql_free_result( $result );
	}

	return $array;
}

?>
