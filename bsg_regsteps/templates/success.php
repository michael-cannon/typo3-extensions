<h1>Registration Successful for BrainStorm <?php echo $this->getVar('confCity'); ?></h1>
<p>Thank you for registering. Below is your agenda. A confirmation email has
been sent.</p>
<p>IMPORTANT: Please be sure to add registrar@brainstorm-group.com and
eventinfo@bpminstitute.org to your address book and trusted sender list to
ensure you receive important notifications around the event.</p>
<p>Call 508-475-0475, x15 if you have any questions.</p>
<?php if ( $this->getVar('isNewMember') ) { ?>
<h1>BPMInstitute.org Membership</h1>
<p>Your membership on BPMInstitute.org has been activated as well, but is not
complete. Please take a moment and use this <a
href="/member-login/?logintype=login&pid=20&redirect_url=/member-login/new-member-profile/secure-new-member-profile.html&user=<?php echo $this->getVar('user'); ?>&pass=<?php echo $this->getVar('pass'); ?>">link to complete your profile</a>, so we may better serve you.</p>
<p>As a reminder, your current login credentials are:</p>
<ul>
	<li>Username: <?php echo $this->getVar('user'); ?></li>
	<li>Password: <?php echo $this->getVar('pass'); ?></li>
</ul>
<?php } ?>
<h1>Your Agenda</h1>
<br />
<?php echo $this->getVar('courses'); ?>