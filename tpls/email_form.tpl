<h3>Email Setup</h3>
<form action="/?emails_menue&action=setup_save" method="post" id="email_form">
    <input type="hidden" name="id" value="<?php echo $setup['id']; ?>">
    <label for="SMTPSecure">SMTP Security</label>
    <input id="SMTPSecure" name="SMTPSecure" type="text" value="<?php echo $setup['SMTPSecure']; ?>" placeholder="TLS"><br>
    <label for="SMTPAuth">SMTP Auth</label>
    <input name="SMTPAuth" type="hidden" value="0">
    <input id="SMTPAuth" name="SMTPAuth" type="checkbox" value="1" <?php if($setup['SMTPAuth'] == '1') echo "checked"; ?>><br>
    <label for="Host">Host</label>
    <input id="Host" name="Host" type="text" value="<?php echo $setup['Host']; ?>"  placeholder="mail.example.com"><br>
    <label for="Port">Port</label>
    <input id="Port" name="Port" type="number" value="<?php echo $setup['Port']; ?>" placeholder="25"><br>
    <label for="Username">Username</label>
    <input id="Username" name="Username" type="text" value="<?php echo $setup['Username']; ?>"><br>
    <label for="Password">Password</label>
    <input id="Password" name="Password" type="password" value="<?php echo $setup['Password']; ?>"><br>
    <label for="FromAddress">From Address</label>
    <input id="FromAddress" name="FromAddress" type="email" value="<?php echo $setup['FromAddress']; ?>"><br>
    <br>
    <input type="submit" value="Submit">
</form>