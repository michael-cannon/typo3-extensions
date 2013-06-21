<script type="text/javascript" src="typo3conf/ext/bsg_regsteps/js/prototype.js"></script>
<script type="text/javascript" src="typo3conf/ext/bsg_regsteps/js/helpers.js"></script>
<style>
#spacer {
    font-size: 0px;
    height: 10px;
}
</style>
<?php
// Draw errors
if($this->hasErrors()) {
    echo "<h3>Errors</h3>";
    echo "<div style='color:red'>";
    foreach ($this->getErrors() as $err) {
        echo $err;
        echo "<br>";
    }
    echo "</div><div id='spacer'></div>";
}
// Get data from controller
if($this->hasVar('form')) {
    $form = $this->getVar('form');
} else {
    $form = array();
}

if( $this->getVar('loggedUser') ) {
    $userData					= $this->userRecordToForm( $this->getVar('loggedUser') );
	$form						= array_merge( $userData, $form );
}

// MLC 20080129 recall pc
if ( isset( $_REQUEST['bsg_step_1'][ 'pc' ] ) ) {
	// form submission
	$form[ 'pc' ]	= $_REQUEST['bsg_step_1'][ 'pc' ];
} elseif ( isset( $_REQUEST[ 'pc' ] ) ) {
	// url query submission
	$form[ 'pc' ]	= $_REQUEST[ 'pc' ];
} elseif ( $this->getVar('loggedUser') ) {
	$form[ 'pc' ]	= $this->getVar('pc');
} else {
	// MLC 20080312 Stacey says no default
	// system default by usergroup level
	$form[ 'pc' ]	= '';
}

// Prepare fields
$flds = array ('first_name', 'last_name','title','company','department', 'address', 'expmonth', 'expyear', 'zip', 'phone', 'fax', 'adminmail', 'pmail', 'bmail', 'state', 'country', 'city', 'ccnumber', 'username', 'cholder', 'buypro' ,'pc', 'conf_series', 'endtime');

foreach ($flds as $f) {
    $$f = isset($form[$f]) ? htmlspecialchars(stripslashes($form[$f])) : null;
}

if(empty($country)) {
    $country = 'USA';
}
?>

<?php echo BSG_REG_HEADER; ?>

<?php if(!$this->getVar('logged')) { ?>

<h3>Contact information</h3>

<p>If you are an existing member, login here to 
automatically load your profile and receive appropriate pricing:</p>

<form action="/member-login/" method="POST">
<table width="175" bgcolor="#cccccc" align="center">
  <tbody>  
  <tr>
    <td><div align="right">Username:</div></td>
    <td><input type="text" size="15" style="width: 100px;" name="user"/></td>
  </tr>
  <tr>
    <td><div align="right">Password:</div></td>
    <td><input type="password" size="15" style="width: 100px;" name="pass"/></td>
  </tr>
  <tr>
    <td></td>
    <td><input type="hidden" value="/index.php?id=<?php echo $this->getCurrentPageId() ?>" name="redirect_url"/>
        <input type="hidden" value="login" name="logintype"/>
        <input type="hidden" value="20" name="pid"/><input type="submit" value="Login" name="submit"/></td>
  </tr>
</tbody></table>
</form>
<?php } ?>
<hr/>
<div id="spacer"></div>
<p>If you are not yet a member, please complete the following contact information.</p>
<form action="/index.php?id=<?php echo $this->getCurrentPageId() ?>" method="POST" enctype="multipart/form-data" onsubmit="return submitForm(this);">
<table width="540">
    <tr>
        <td width="175">First Name:</td>
        <td><input type="text" name="bsg_step_1[first_name]" value="<?php echo $first_name ?>" style="width: 220px; background-color: rgb(204, 204, 204);"></td>
    </tr>
    <tr>
        <td width="175">Last Name:</td>
        <td><input type="text" name="bsg_step_1[last_name]" value="<?php echo $last_name ?>" style="width: 220px; background-color: rgb(204, 204, 204);"></td>
    </tr>
    
    <tr>
        <td>Title:</td>
        <td><input type="text" name="bsg_step_1[title]" value="<?php echo $title ?>" style="width: 220px; background-color: rgb(204, 204, 204);"></td>
    </tr>
    <tr>
        <td>Company:</td>
        <td><input type="text" name="bsg_step_1[company]" value="<?php echo $company ?>" style="width: 220px; background-color: rgb(204, 204, 204);"></td>
    </tr>
    <tr>
        <td>Department:</td>
        <td><input type="text" name="bsg_step_1[department]" value="<?php echo $department ?>" style="width: 220px; background-color: rgb(204, 204, 204);"></td>
    </tr>
    <tr>
        <td>Country:</td>
        <td>
        <select style="width:220px" onchange="javascript:getStates()" id="country" name="bsg_step_1[country]">
<?php
foreach ($this->getVar("countries") as $key=>$c) {
    $selected = $key == $country ? ' selected="selected" ': '';
    $title = trim(utf8_encode($c['cn_short_en']));
    if(!empty($title)) {
        echo "<option value='{$c['cn_iso_3']}'{$selected}>{$title}</option>";
    }
}
?>                    
        </select>
        </td>
    </tr>
    <tr>
        <td>Address:</td>
        <td><textarea name="bsg_step_1[address]" style="width: 220px; height:60px"><?php echo $address ?></textarea></td>
    </tr>
    <tr>
        <td>City:</td>
        <td><input type="text" name="bsg_step_1[city]" style="width: 220px; background-color: rgb(204, 204, 204);" value="<?php echo $city ?>"></td>
    </tr>
    
    <tr>
        
        <td>State/province:</td>
        <td>        
        <div id="states">
        <select style="width: 220px;" name="bsg_step_1[state]" id="state"></select>
        </div>
        </td>
    </tr>
     <tr>
        <td>Zip:</td>
        <td><input type="text" name="bsg_step_1[zip]"  value="<?php echo $zip ?>" style="width: 220px; background-color: rgb(204, 204, 204);"></td>
    </tr>
    <tr>
        <td>Phone:</td>
        <td><input type="text" name="bsg_step_1[phone]"  value="<?php echo $phone ?>" style="width: 220px; background-color: rgb(204, 204, 204);"></td>
    </tr>
    <tr>
        <td>Fax:</td>
        <td><input type="text" name="bsg_step_1[fax]"  value="<?php echo $fax ?>" style="width: 220px; background-color: rgb(204, 204, 204);"></td>
    </tr>    
    <tr>
        <td>Business email for<br>verification:</td>
        <td><input type="text" name="bsg_step_1[bmail]"  value="<?php echo $bmail ?>" style="width: 220px; background-color: rgb(204, 204, 204);"></td>
    </tr>    
    <tr>
      <td colspan="2"><p>
            <br/>Please note: For verfication purposes, we require your business email address. 
            Use the field below to enter your preferred email for contact. We will only use your preferred email. 
            If it is the same email, please enter in both fields. 
            <br/>
            <br/></p>
      </td>
    </tr>    
    <tr>
        <td>Preferred email:</td>
        <td><input type="text" name="bsg_step_1[pmail]"  value="<?php echo $pmail ?>" style="width: 220px; background-color: rgb(204, 204, 204);">
		<p>The email of the person attending is required in this field.</p></td>
    </tr>
    <tr>
        <td>Administrative email:</td>
        <td><input type="text" name="bsg_step_1[adminmail]"  value="<?php echo $adminmail ?>" style="width: 220px; background-color: rgb(204, 204, 204);">
		<p>If cc of important communication desired, enter email in this field.</p></td>
    </tr>

</table>

<div id="spacer"></div>
<h3>
Build your agenda
</h3>

<p><b>Select two workshops OR 1 training course OR 1 conference package PER DAY</b></p>

<table width="540">
    <tr>
        <td colspan="2"></td>
	</tr>
    <tr>
        <td style="text-align: right; padding-right: 1em; width: 220px;"><b>Priority code</b></td>
        <td><input type="text"  style="width: 220px; background-color: rgb(204, 204, 204);" name="bsg_step_1[pc]" id="pc" value="<?php echo $pc ?>" onchange="javascript:pcClick()" onmouseout="javascript:void(0)" onblur="javascript:pcClick()" style="width:220px"></td>
    </tr>    

<?php 
$courses = $this->getVar('courses');
$idGroups = array();
$coursesAmount = count($courses);
for($i=0; $i<$coursesAmount; $i++) {
    $row =& $courses[$i];
    $idGroup = 'course_'.strval($i+1);
    $idGroups[] = $idGroup;
	$background	= ( isset( $row['background'] ) )
		? "background-color: {$row['background']};"
		: ''
		;
?>
   
    <tr style="vertical-align: top; <?php echo $background; ?>">
        <td style="text-align: right; padding-right: 1em;"><?php echo $row['title'] ?></td>
        <td style="height: 40px;">
        	<?php echo $row['subtitle'] ?><br/>
            <select id="<?php echo $idGroup ?>" name="bsg_step_1[<?php echo $idGroup ?>]" size="1" onchange='checkCourseConfig("<?php echo $idGroup; ?>"); courseChange(this);' style="width:368px">
<?php
$wasSelected = false;
foreach ($row['courses'] as $course) {
    $selected = $course['uid'] == $form[$idGroup] ? ' selected="selected" ': '';
    if($selected) {
        $wasSelected = true;
    }
    echo "<option value='{$course['uid']}'{$selected}>{$course['name']}</option>";
}
$selected = $wasSelected ? '' :  ' selected="selected" ';
echo "<option value='0'{$selected}>None</option>";
?>
            </select>               
        </td>
    </tr>    
<?php } ?>    
</table>

<div id="spacer"></div>
<h3>Professional Membership</h3>
<?php
	$checked = $buypro ? ' checked="checked" ' : '';
	$expiry	= date( 'F j, Y', $endtime );
?>
<?php if(!$this->getVar('professional')) { ?>
	<p><input type="checkbox" id="buypro" onchange="javascript:onPro()" name="bsg_step_1[buypro]"<?php echo $checked ?> value="1">
	Purchase 1-year of professional membership
	</p>
<?php } else { ?>
	<p><input type="checkbox" id="buypro" onchange="javascript:onPro()" name="bsg_step_1[buypro]"<?php echo $checked ?> value="2">
	Renew for another year of professional membership. It currently expires
	<?php echo $expiry; ?>.
	</p>
<?php } ?>    
<p><b>CORPORATE MEMBERS Please note:</b> You must be a current Professional Member in order to receive
Corporate rates.  In the event this box is intentionally unchecked AND you
are not a current Professional Member, $149 will be added to your
transaction after registration and your Professional Membership will be
activated.</p>

<div id="spacer"></div>
<h3>Payment preview</h3>
<div id="prices"><p>Select items you are interested in and get payment preview in this box</p></div>
 
<div style="height:10px;font-size:0px">&nbsp;</div>
<h3>Payment information:</h3>
<table width="100%">
<tr>
    <td width="175">Credit cards accepted</td>
    <td>
        American Express,
        Discover,
        MasterCard, &
        Visa
    </td>
  </tr>
    <tr>
        <td width="175">Cardholder name:</td>
        <td><input type="text"  style="width: 220px; background-color: rgb(204, 204, 204);" name="bsg_step_1[cholder]" value="<?php echo $cholder ?>" style="width:220px"></td>
    </tr>
    <tr>
        <td width="175">Credit card number:</td>
        <td><input type="text"  style="width: 220px; background-color: rgb(204, 204, 204);" name="bsg_step_1[ccnumber]" value="<?php echo $ccnumber ?>" style="width:220px"></td>
    </tr>
    <tr>
        <td width="175">Expiration date:</td>
        <td><select name="bsg_step_1[expmonth]">
<?php
foreach ($this->getVar("months") as $c) {
    $selected = $c['uid'] == $expmonth ? ' selected="selected" ': '';
    echo "<option value='{$c['uid']}'{$selected}>{$c['name']}</option>";
}
?>         
          </select>
          <select name="bsg_step_1[expyear]">
<?php
foreach ($this->getVar("years") as $c) {
    $selected = $c == $expyear ? ' selected="selected" ': '';
    echo "<option value='{$c}'{$selected}>{$c}</option>";
}
?>         
          </select>          
        </td>        
    </tr>
</table>
<div id="spacer"></div>
<table width="540">   
    <tbody>
    <tr>
        <td colspan="2">
<?php
    $prefixId = $this->getVar('prefixId');
    $recaptcha = $this->getVar('recaptcha');
	echo $recaptcha;
	echo '<input type="hidden" name="'.$prefixId.'[submitted]" value="1" />';
?>
		</td>
    </tr>
	<tr>
        <td><input type="checkbox" id="agree" checked="checked" /></td>
        <td><p>I agree to be bound by this website's <a target="_blank" href="/privacy.html">Terms of Use and Privacy Policy</a>. </p>
          </td>
    </tr>
    <tr>
      <td colspan="2"><p class="all-caps"><b><font color="#ff0000">Please Scan Over The Form Before Submitting</font></b></p></td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="submit" value="Continue"/>
		</td>
    </tr>
</tbody></table>


</form>

<p>
 * These rates are for End-User Practitioner Organizations only.  Non-sponsoring Solution Providers, consultants, etc. are ineligible and may register using the $2,500 non-sponsoring Solution Provider package. Companies interested in sponsoring should contact sponsor@bpminstitute.org.
<br/>
<br/>
Payment Policy:
</p>
<p>Payment is due in full at the time of registration.  If you have any questions, please call Client Services at 508-475-0475 ext. 15.</p>
<p/>
<p>Alternate ways to register:</p>
<p>
Alternatively to online submission, you may print this form and fax to 508-475-0466 or mail to BrainStorm Group, Inc. 45 Lyman Street, Suite 23, Westborough, MA 01581. You may also register via phone by calling 508-475-0475 ext. 15.
<br/>
<br/>
Cancellation policy:
<br/>
BrainStorm Group will accept substitute registrants at any time or gladly transfer your registration to another BrainStorm Group event. No refunds will be given. Discounts may not be applied to previous registrations. BPMInstitute.org reserves the right to make final determination.
</p>

<!-- javascripts -->
<script type="text/javascript">
<!--

<?php echo BSG_REG_COURSE_CHECK; ?>

var coursesCount = <?php echo $coursesCount = count($courses) ?>;
var loggedIn = <?php if($this->getVar('logged')) { echo 'true'; } else { echo 'false'; } ?>;
var firstRun = <?php if($this->getVar('firstRun')) { echo 'true'; } else { echo 'false'; } ?>;
var profMember = <?php if($this->getVar('professional')) { echo 'true'; } else { echo 'false'; } ?>;
// -->
</script>

<!-- javascripts -->
<script type="text/javascript">
<!--

function getIds() {
    var res = '';
    coursesCount = <?php echo $coursesCount ?>;
    for(i=0; i < coursesCount; i++) {
        j=i+1;
        value=document.getElementById('course_'+j).value;
        res = res + value;
        if(i!= coursesCount - 1) {
            res = res + ',';
        }
    }
    return res;
}
// -->
</script>

<!-- javascripts -->
<script type="text/javascript">
<!--


function courseChange(obj) {
    if(checkSelections(obj)) {
	    getPrices();
      }
}

function getPC() {
    return document.getElementById('pc').value;
}

function getPro() {
    return document.getElementById('buypro').checked ? document.getElementById('buypro').value : '0';
}

// -->
</script>

<!-- javascripts -->
<script type="text/javascript">
<!--
function statesComplete(request) {
	// alert('statesComplete');
    var oldSelections = '<?php echo $state ?>';
    var outputDiv = document.getElementById('states');
    // alert(request.responseText);
    error = request.responseXML.getElementsByTagName('error');
    if(error[0]) { alert(error[0].firstChild.data); return; }
    content = request.responseXML.getElementsByTagName('content');
    outputDiv.innerHTML = content[0].firstChild.data;

    element = document.getElementById('state');
    options = element.getElementsByTagName('option');

    for(i=0, iCount = options.length; i<iCount; i++) {
        if(oldSelections == options[i].value) {
            options[i].selected = 'selected';
        }
    }
}

function getStates() {
    var uri = 'index.php?id=<?php echo $this->getCurrentPageId() ?>&cmd=zones&no_cache=1';
    var country = document.getElementById("country").value;
    var params = 'c='+country;
    var fetchErrorMessage = 'Error fetching data from server';
    var outputDiv = document.getElementById('states');
	// alert('http://www.bpminstitute.org/' + uri + '&' + params);

    var ajaxRequest = new Ajax.Request(
    uri,
    {
    'method': 'post',
    'parameters': params,
    'onComplete': statesComplete
    });
}
// -->
</script>

<!-- javascripts -->
<script type="text/javascript">
<!--

function pricesComplete(request) {
    var outputDiv = document.getElementById('prices');
    // alert(request.responseText);
    error = request.responseXML.getElementsByTagName('error');
    if(error[0]) { alert(error[0].firstChild.data); return; }
    content = request.responseXML.getElementsByTagName('content');
    outputDiv.innerHTML = content[0].firstChild.data;
}

function pricesLoading() {
    var outputDiv = document.getElementById('prices');
    var previewMessage ="<p>Loading price preview...</p>";
    outputDiv.innerHTML = previewMessage;
}

// -->
</script>

<!-- javascripts -->
<script type="text/javascript">
<!--
function getPrices() {
    var uri = 'index.php?id=<?php echo $this->getCurrentPageId() ?>&cmd=prices&no_cache=1';
    var params = 'ids='+getIds()+'&pc='+getPC()+"&bp="+getPro();
    var fetchErrorMessage = 'Error fetching data from server';

    var ajaxRequest = new Ajax.Request(
    uri,
    {
    'method': 'post',
    'parameters': params,
    'onComplete': pricesComplete,
    'onLoading': pricesLoading
    });
}

function pcClick() {
    if(getPC().length > 3) {
		getAutoBuypro( true );
    	getPrices();
    }
}

// -->
</script>

<!-- javascripts -->
<script type="text/javascript">
<!--
function onPro() {
    getPrices();
}

function checkSelections(obj) {
	var error1 = 'You cannot select both 1 and 2 day conferences - please select a 2-day conference package';
	var error2 = 'You can not select multiple 1-day conference packages - please select a 2-day conference package.';
	var error3 = "There's a scheduling conflict with your 2-day course - please check your conference and training selections.";
	var error4 = "There's a scheduling conflict with your 2-day confernece - please check your conference and training selections.";

	// HDAY1, HDAY2 may need errors
    var arrConflicts = { 
		0 : {
			'ids': new Array('2DAYS','2DAYSNS','DAY2')
			, 'message': error1
		}
		, 1 : {
			'ids': new Array('DAY1','DAY2')
			, 'message': error2
		}
		, 2 : {
			'ids': new Array('DAY1', '2DAYS', '2DAYSNS', <?php echo BSG_REG_COURSE_CONFLICT_CONF_DAY1; ?>)
			, 'message': error4
		}
		, 3 : {
			'ids': new Array('DAY2', '2DAYS', '2DAYSNS', <?php echo BSG_REG_COURSE_CONFLICT_CONF_DAY2; ?>)
			, 'message': error4
		}
		, 4 : {
			'ids': new Array(<?php echo BSG_REG_COURSE_CONFLICT_GENERAL; ?>)
			, 'message': error3
		}
	};
    var options = 0;
    for(i in arrConflicts) {
		if(typeof(i) != 'function') {
			options+=1;
		}
    }
    
    var coursesCount = <?php echo intval($coursesAmount) ?>;
    var ids = new Array();
    for(i=1; i<= coursesCount; i++) {        
        id = 'course_'+i;
		ids.push(document.getElementById(id).value);
    }

    var found;
    for(i=0; i<options; i++) {
		forbidden = arrConflicts[i]['ids'];
		message = arrConflicts[i]['message'];
		found = new Array();
		idsl = ids.length;
		for(j = 0; j < idsl; j++) {		
			if(inArray(forbidden, ids[j])) {
				found.push(ids[j]);
			}
		}
		// let 2-day conf come up twice
		found = found.unique();
		if(found.length > 1) {
			alert(message);
			obj.options[obj.options.length-1].selected = true;
			return false;	
		}
    }

   return true;
}
// -->
</script>

<!-- javascripts -->
<script type="text/javascript">
<!--

function submitForm(obj) {    
    var coursesCount = <?php echo intval($coursesAmount) ?>;
    
    var allEmpty = true;
    var hasConference = false;
	var cDay = new RegExp('DAY');

    for(i=1; i<= coursesCount; i++) {        
        id = 'course_'+i;

        if(document.getElementById(id).value != 0) {
           allEmpty = false;
        }        

		var cValue = document.getElementById(id).value;
		var cResult = cDay.exec(cValue);
        if(null != cResult) {
			hasConference = true;
        }        
    }

    if(allEmpty) {
       alert('Please select at least one course to proceed');
	   document.getElementById('course_1').focus()
       return false;
    }   

	if(hasConference && document.getElementById('conf_series').value == '') {
       alert('Please select your conference series');
	   document.getElementById('conf_series').focus()
       return false;
    }   
    
    if(document.getElementById('state').value == 'PCO') {
       alert('Please select a valid state to proceed');
	   document.getElementById('state').focus()
       return false;
    }

    if(document.getElementById('agree').checked) {
        return true;
    } else {
		alert('You must agree with terms and conditions');
	   document.getElementById('agree').focus()
		return false;
	}
}
// -->
</script>

<!-- javascripts -->
<script type="text/javascript">
<!--

function getAutoBuypro( run ) {
	if ( run ) {
		var uri = 'index.php?id=<?php echo $this->getCurrentPageId() ?>&cmd=buypro&no_cache=1';
		var params = 'pc='+getPC();
		var fetchErrorMessage = 'Error fetching data from server';

		var ajaxRequest = new Ajax.Request(
		uri,
		{
			'method': 'post',
			'parameters': params,
			'onComplete': buyproComplete
		});
	}
       
	return;
}

// -->
</script>

<!-- javascripts -->
<script type="text/javascript">
<!--
function buyproComplete(request) {
    error = request.responseXML.getElementsByTagName('error');
    if(error[0]) { alert(error[0].firstChild.data); return; }
   
    content = request.responseXML.getElementsByTagName('content');
    if ( 1 == content[0].firstChild.data )
	{
	    	document.getElementById('buypro').checked = true;
		getPrices();
	}
}
// -->
</script>

<!-- javascripts -->
<script type="text/javascript">
<!--

getStates();
getAutoBuypro( firstRun );
getPrices();
// -->
</script>