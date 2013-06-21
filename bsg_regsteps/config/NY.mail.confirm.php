<?php
$strData = <<<EOT
{$arrData['company']}
{$arrData['address']}
{$arrData['city']}, {$arrData['country']}, {$arrData['zip']}
 
Dear {$arrData['first_name']}:

This is your receipt and confirmation* for your registration to BrainStorm New York to be held November 2-5, 2009 at the Westin Times Square.

Please bring a copy of this email and a business card on-site to our registration area to pickup your badge and Training Material. 
        
Your agenda:
BrainStorm New York Selections: {$conf_series}
{$courses}

{$proAccount}

Paid in full by cardholder: {$payData['userData']['cholder']}
Total amount paid: $ {$total} 
Priority code used: {$priorityCode}

{$login}
 
Important Information: 
Registration Opens at 7:00 AM each day.  Please arrive early to gather your materials.

Host Hotel: BrainStorm New York will be held at The Westin Times Square Hotel. A limited block of rooms has been reserved for our attendees at the group rate of $329 single/double until October 8. To make your hotel reservation, please call the The Westin reservations department toll free in the US at 888-627-7149 or 212-201-2700 outside the US. Be sure to mention Group Booking Code: BrainStorm Group when making your reservation. This rate is based on availability and cannot be guaranteed after October 8, 2008. Early booking is strongly advised.
 
Please be aware that Training on November 3rd and 4th includes access to the Conference Keynotes and Reception. 
 
Attire: Business casual, comfortable attire is recommended. Please bring a light jacket or sweater, as meeting room temperatures may vary.
 
Registration Terms & Conditions: We will accept substitute registrants at any time or gladly transfer your registration to another event. No refunds are given.

If you have any questions, please call me at 508-475-0475 ext.15 between 9am and 5pm eastern.  

Thank you for your registration. We look forward to hosting you at BrainStorm New York!
 
Sincerely,
Jane E. Waring~Pelkey
Client Services Manager
508-475-0475, x15
registrar@brainstorm-group.com
www.BPMInstitute.org
www.SOAInstitute.org
EOT;

?>