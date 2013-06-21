<?php
$strData = <<<EOT
{$arrData['company']}
{$arrData['address']}
{$arrData['city']}, {$arrData['country']}, {$arrData['zip']}
 
Dear {$arrData['first_name']}:

This is your receipt and confirmation* for your registration to BrainStorm Chicago to be held April 6-9, 2009.

Please bring a copy of this email and a business card on-site to our registration area to pickup your badge and Training Material. 
        
Your agenda:
Conference series: {$conf_series}
{$courses}

{$proAccount}

Paid in full by cardholder: {$payData['userData']['cholder']}
Total amount paid: $ {$total} 
Priority code used: {$priorityCode}

{$login}
 
Important Information: 
Registration Opens at 7:00 AM each day.  Please arrive early to gather your materials.
  
Recommended Attire: Business casual.
 
Cancellation Policy: We will accept substitute registrants at any time or gladly transfer your registration to another event. No refunds are given.

Registration terms and conditions: All registrations are subject to review.  BPMInstitute.org reserves the right to make the final determination.

If you have any questions, please call 508-475-0475 ext.15 between 9am and 5pm eastern.  

Thank you for your registration. We look forward to hosting you at BrainStorm Chicago!
 
Sincerely,
Jane E. Waring~Pelkey
Client Services Manager
508-475-0475, x15
registrar@brainstorm-group.com
www.BPMInstitute.org
www.SOAInstitute.org
EOT;

?>