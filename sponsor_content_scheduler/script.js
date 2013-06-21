	function sponsorChanged()   {
	
	sponsor_menu = document.getElementsByName('tx_sponsorcontentscheduler_pi1[sponsor_id]')[0].options[document.getElementsByName('tx_sponsorcontentscheduler_pi1[sponsor_id]')[0].selectedIndex].value;
	
	if (sponsor_menu==-1){
		setDefault();
		return;
	}

	setSponsorUser(sponsor_menu);

}


function setDefault()
{
	sponsor_menu = document.getElementsByName('tx_sponsorcontentscheduler_pi1[sponsor_id]')[0];
	sponsor_user_menu = document.getElementsByName('tx_sponsorcontentscheduler_pi1[sponsor_user_id]')[0];
	
	
	deleteOptions(sponsor_user_menu)
	
	sponsor_menu.options[0].selected = true;
	sponsor_user_menu.options[0] = new Option('----SELECT----', 0)
	sponsor_user_menu.options[0].selected = true;
	
}

function setSponsorUser(sponsor_menu_value)
{

		//second dropdown
	sponsor_user_menu = document.getElementsByName('tx_sponsorcontentscheduler_pi1[sponsor_user_id]')[0];

		// remove all values from second dropdown
	deleteOptions(sponsor_user_menu);


		// populate second dropdown by reading from the js array sponsorUserTable
	var rows, i, flag, kount;
	flag = 1;
		
	rows = sponsorUserTable.length;

	i = 0;
	kount = 0;

		// put default values
	sponsor_user_menu.options[0] = new Option('----SELECT----', 0);
	sponsor_user_menu.options[0].selected = true;
	
	kount++;
	do 	{
			// match 1st dropdown value with array  
		if (sponsor_menu_value == sponsorUserTable[i][2])		{
			sponsor_user_menu.options[kount] = new Option(sponsorUserTable[i][1],sponsorUserTable[i][0]);
			//sponsorUserTable.options[kount] = new Option(sponsorUserTable[i][1],sponsorUserTable[i][0]);
			kount++;
			flag=0
		}
		i++;
	} while (i < rows)

}


function deleteOptions(xDropDown)
{
	var tot = xDropDown.options.length
	for (i=0; i < tot ; i++)
	{
		xDropDown.options[i]=null
	}
	xDropDown.options.length=0;
}


// Code for pacakage

	function packageChanged()   {
	
	sponsor_menu = document.getElementsByName('tx_sponsorcontentscheduler_pi1[sponsor_id]')[0].options[document.getElementsByName('tx_sponsorcontentscheduler_pi1[sponsor_id]')[0].selectedIndex].value;
	
	if (sponsor_menu==-1){
		setDefaultValue();
		return;
	}

	setSponsorUserPackage(sponsor_menu);

}


function setDefaultValue()
{
	sponsor_menu = document.getElementsByName('tx_sponsorcontentscheduler_pi1[sponsor_id]')[0];
	sponsor_user_package_menu = document.getElementsByName('tx_sponsorcontentscheduler_pi1[package_id]')[0];
	
	
	deleteOptions(sponsor_user_package_menu)
	
	sponsor_menu.options[0].selected = true;
	sponsor_user_package_menu.options[0] = new Option('----SELECT----', 0)
	sponsor_user_package_menu.options[0].selected = true;
	
}

function setSponsorUserPackage(sponsor_menu_value)
{
	
		//second dropdown
	sponsor_user_package_menu = document.getElementsByName('tx_sponsorcontentscheduler_pi1[package_id]')[0];

		// remove all values from second dropdown
	deleteOptions(sponsor_user_package_menu);


		// populate second dropdown by reading from the js array sponsorUserTable
	var rows, i, flag, kount;
	flag = 1;
		
	rows = packageTable.length;

	i = 0;
	kount = 0;

		// put default values
	sponsor_user_package_menu.options[0] = new Option('----SELECT----', 0);
	sponsor_user_package_menu.options[0].selected = true;
	
	kount++;
	do 	{
			// match 1st dropdown value with array  
		if (sponsor_menu_value == packageTable[i][2])		{
			sponsor_user_package_menu.options[kount] = new Option(packageTable[i][1],packageTable[i][0]);
			//sponsorUserTable.options[kount] = new Option(sponsorUserTable[i][1],sponsorUserTable[i][0]);
			kount++;
			flag=0
		}
		i++;
	} while (i < rows)

}

