<?php
/**
 * Language labels for plugin "tx_newloginbox_pi1"
 * 
 * TYPO3 CVS ID: $Id: locallang.php,v 1.1.1.1 2010/04/15 10:03:53 peimic.comprock Exp $
 * This file is detected by the translation tool.
 */

$LOCAL_LANG = Array (
	'default' => Array (
		'oLabel_header_welcome' => 'User Login',
		'oLabel_msg_welcome' => 'Enter your username and password here in order to log in on the website:',
		'oLabel_header_logout' => 'You have logged out.',
		'oLabel_msg_logout' => 'You just logged out from your user session on this website. You can login again or as another user by the form below.',
		'oLabel_header_error' => 'Login failure',
		'oLabel_msg_error' => 'An error occurred during login. Most likely you didn\'t enter the username or password correctly. Be certain that you enter them precisely as they are, including upper/lower case. Another possibility is that cookies might be disabled in your webbrowser.',
		'oLabel_username_error' => 'The username entered is not found. Please double-check your spelling.',
		'oLabel_password_error' => 'The password entered is incorrect. Please note that passwords are case sensitive.',
		'oLabel_domain_error' => 'Access to this site is denied. Please double-check the site domain name for which you are trying to log into.',
		'oLabel_header_success' => 'Login successful',
		'oLabel_msg_success' => 'You are now logged in as \'###USER###\'',
		'oLabel_header_status' => 'Current status',
		'oLabel_msg_status' => 'This is your current status:',
		'cookie_warning' => 'Warning: There is a possibility that cookies are
		not enabled in your web browser. If your login disappears on your next
		click, then you should enable cookies or accept cookies from this
		website and relogin.</p>
		<p>Please try these quick steps for your browser before contacting us to ensure your computer is setup correctly:</p>
<h4>Enabling Cookies in Internet Explorer 6.x:</h4>
<ol>
<li> Select "Internet Options" from the Tools menu.</li>
<li> Select the "Privacy" tab.</li>
<li> Under "Web Sites", click the "Edit" button</li>
<li> In the textbox for "Address of the Web site" enter: *.bpminstitute.org</li>
<li> Click the "Allow" button. Make sure "bpminstitute.org Always Allow" is in the textbox under Managed Web sites</li>
<li> Click "OK" to save your changes and exit the dialog box.</li>
</ol>

<h4>Enabling Cookies in Internet Explorer 5.x:</h4>
<ol>
<li> Select "Internet Options" from the Tools menu.</li>
<li> Select the Security tab, then click the "Custom Level" button.</li>
<li> Locate the "Cookies" section, then select "Enable" for both Cookies options.</li>
</ol>

<h4>Enabling Cookies in Internet Explorer 4.x:</h4>
<ol>
<li> Select "Internet Options" from the View menu.</li>
<li> Click the "Advanced" tab, then scroll down to "Security."</li>
<li> Click "Always accept cookies."</li>
<li> Click "OK"</li>
</ol>

<h4>Enabling Cookies in Netscape:</h4>
<ol>
<li> Select "Preferences" from the Edit menu.</li>
<li> Click "Advanced" in the "Category" portion of the window.</li>
<li> Under the "Cookies" area, click either "Accept all cookies," or "Accept only cookies that get sent back to the originating server."</li>
<li> Click "OK." </li>
</ol>
<p>&nbsp;
		',
		'username' => 'Username:',
		'password' => 'Password:',
		'login' => 'Login',
		'logout' => 'Logout',
		'send_password' => 'Send password',
		'your_email' => 'Your email:',
		'your_username' => 'Your username:',
		'forgot_password' => 'Forgot your password?',
		'forgot_password_pswmsg' => 'Password Reminder for %s
Your username is %s
Your password is %s
',
		'forgot_password_no_pswmsg' => '<p>We are very sorry, but we do not
		recognize the information you entered.</p>
		<p>If you may have not yet created a Username and Password - use our <a href="/join">Join form</a> rather than this conversion page.</p>',
		'forgot_password_enterEmail' => 'Please enter EITHER the Username OR Email under which you registered your account. Then click "send password" and your password will be emailed.',
		'forgot_password_emailSent' => 'Your password has now been sent to the email address %s',
		'forgot_password_backToLogin' => 'Please re-try.',
	),
	'dk' => Array (
		'oLabel_header_welcome' => 'Brugerlogin',
		'oLabel_msg_welcome' => 'Indtast dit brugernavn og password her for at logge ind på websitet:',
		'oLabel_header_logout' => 'Du har nu logget ud.',
		'oLabel_msg_logout' => 'Du har netop logget ud fra dit ophold som bruger på dette website. Du kan logge ind igen med formen herunder.',
		'oLabel_header_error' => 'Fejl i login',
		'oLabel_msg_error' => 'Der skete en fejl under login proceduren. Højst sandsynligt har du ikke indtastet brugernavn og password korrekt.
Vær sikker på, at indtaster dem helt rigtigt inklusiv store og små bogstaver.
En anden mulighed kan være, at du har slået \'cookies\' fra i din browser.',
		'oLabel_header_success' => 'Login succesfuldt',
		'oLabel_msg_success' => 'Du er nu logget in som \'###USER###\'',
		'oLabel_header_status' => 'Nuværende status',
		'oLabel_msg_status' => 'Dette er din nuværende status:',
		'cookie_warning' => 'Advarsel: Der er en sandsynlighed for at cookies ikke er slået til i din browser! Hvis dit login forsvinder ved næste klik så er det tilfældet og du bør slå cookies til (eller acceptere cookies fra dette website) med det samme!',
		'username' => 'Brugernavn:',
		'password' => 'Password:',
		'login' => 'Log ind',
		'logout' => 'Log ud',
		'send_password' => 'Send password',
		'your_email' => 'Din email:',
		'forgot_password' => 'Har du glemt dit password?',
		'forgot_password_pswmsg' => 'Dit password
Hej %s

Dit brugernavn er "%s"
Dit password er "%s"',
		'forgot_password_no_pswmsg' => 'Dit password
Hej %s

Vi kunne ikke finde et brugernavn tilhørende denne email-adresse og kan derfor ikke sende et password til dig. Du har formentlig stavet din email adresse forkert (store og små bogstaver betragtes som forskellige) eller også har du ikke registreret dig selv endnu?',
		'forgot_password_enterEmail' => 'Indtast venligst den email-adresse som du er registreret med. Tryk derefter "Send password" og du vil straks modtage dit password pr. email. Vær sikker på, at du staver din email adresse rigtigt.',
		'forgot_password_emailSent' => 'Dit password er nu blevet sendt til email adressen %s',
		'forgot_password_backToLogin' => 'Tilbage til login formular',
	),
	'de' => Array (
		'oLabel_header_welcome' => 'Benutzer Anmeldung',
		'oLabel_msg_welcome' => 'Geben Sie Ihren Benutzernamen und Ihr Passwort ein, um sich an der Webseite anzumelden:',
		'oLabel_header_logout' => 'Sie haben sich abgemeldet.',
		'oLabel_msg_logout' => 'Sie haben sich von dieser Webseite abgemeldet. Sie können sich erneut, auch als ein anderer Benutzer, mit dem unten angezeigten Formular anmelden.',
		'oLabel_header_error' => 'Anmeldefehler',
		'oLabel_msg_error' => 'Ein Fehler trat wärend der Anmeldung auf. Wahrscheinlich haben Sie Ihren Benutzernamen oder das Passwort falsch eingegeben.<br>Vergewissern Sie sich, dass Sie beide Angaben korrekt eingegeben haben - Groß-/Kleinschreibung wird unterschieden.<br>Eine andere Möglichkeit ist, dass Sie eventuell die Cookies in Ihrem Webbrowser deaktiviert haben.',
		'oLabel_header_success' => 'Anmeldung erfolgreich',
		'oLabel_msg_success' => 'Sie sind nun angemeldet als \'###USER###\'',
		'oLabel_header_status' => 'Aktueller Status',
		'oLabel_msg_status' => 'Das ist Ihr aktueller Status:',
		'username' => 'Benutzername:',
		'password' => 'Passwort:',
		'login' => 'Anmelden',
		'logout' => 'Abmelden',
		'send_password' => 'Passwort senden',
		'your_email' => 'Ihre Email:',
		'forgot_password' => 'Passwort vergessen?',
		'forgot_password_pswmsg' => 'Ihr Passwort
Hallo %s

Ihr Benutzername ist "%s"
Ihr Passwort ist "%s"',
		'forgot_password_no_pswmsg' => 'Ihr Passwort
Hallo %s

Wir konnten keine mit dem angegeben Benutzernamen verknüpfte Email-Adresse finden. Aus diesem Grund können wir Ihnen kein Passwort zusenden. Vielleicht haben Sie Ihre Email-Adresse fehlerhaft eingegeben (Groß-/Kleinschreibug wird unterschieden!) oder Sie haben sich eventuell noch garnicht registriert.',
		'forgot_password_enterEmail' => 'Bitte geben Sie die Email Adresse ein, mit der sich registiert haben. Anschließend klicken Sie auf "Passwort senden" und Ihr Passwort wird Ihnen umgehend zugesendet. Achten Sie auf die korrekte schreibweise Ihrer Email-Adresse.',
		'forgot_password_emailSent' => 'Ihr Passwort wurde nun zur Email Adresse %s gesendet',
		'forgot_password_backToLogin' => 'Zurück zum Anmeldeformular',
	),
	'no' => Array (
		'oLabel_header_welcome' => 'Brukerinnlogging',
		'oLabel_msg_welcome' => 'Tast inn ditt brukernavn og passord her for å logge inn på nettstedet:',
		'oLabel_header_logout' => 'Du har nå logget ut.',
		'oLabel_msg_logout' => 'Du har nettopp logget ut fra ditt opphold som bruker på nettstedet. Du kan logge inn igjen ved å benytte skjemaet under.',
		'oLabel_header_error' => 'Innlogging feilet',
		'oLabel_msg_error' => 'Noe gikk galt under innloggingsprosedyren. Mest sannsynlig har du ikke tastet brukernavn og passord korrekt.<BR>Forsikre deg om at du taster dem helt riktig inklusive store og små bokstaver.<BR>En annen mulighet kan være at informasjonskapsler (cookies) er avslått i din nettleser.',
		'oLabel_header_success' => 'Innlogging vellykket',
		'oLabel_msg_success' => 'Du er nå logget inn som \'###USER###\'',
		'oLabel_header_status' => 'Nåværende status',
		'oLabel_msg_status' => 'Dette er din nåværende status',
		'username' => 'Brukernavn:',
		'password' => 'Passord:',
		'login' => 'Logg inn',
		'logout' => 'Logg ut',
		'send_password' => 'Send passord',
		'your_email' => 'Din e-post:',
		'forgot_password' => 'Har du glemt passordet ditt?',
		'forgot_password_pswmsg' => 'Ditt passord
Hei %s

Ditt brukernavn er "%s"
Ditt passord er "%',
		'forgot_password_no_pswmsg' => 'Ditt passord
Hei %s

Vi kunne ikke finne et brukernavn tilhørende denne e-post adressen og kan derfor ikke sende et passord til deg. Du har muligens stavet din e-post adresse feil (det skilles mellom store og små bokstaver) eller du har kanskje ikke registrert deg enda?',
		'forgot_password_enterEmail' => 'Vennligst tast inn den e-post adressen som du er registrert med. Trykk deretter på "Send passord" og du vil straks motta passordet ditt pr. e-post. Pass på at du staver e-post adressen din riktig.',
		'forgot_password_emailSent' => 'Passordet ditt har nå blitt sendt til e-post adressen %s',
		'forgot_password_backToLogin' => 'Tilbake til innloggingsskjemaet',
	),
	'it' => Array (
		'oLabel_header_welcome' => 'Login utente',
		'oLabel_msg_welcome' => 'Inserisci username e password per effettuare il login:',
		'oLabel_header_logout' => 'Hai effettuato il logout.',
		'oLabel_msg_logout' => 'La tua sessione su questo sito è terminata. Puoi effettuare un nuovo login utilizzando il form sottostante.',
		'oLabel_header_error' => 'Errore di login',
		'oLabel_msg_error' => 'Errore durante il login. Probabilmente non hai inserito correttamente lo username o la password.<br>Assicurati di averli digitati in modo corretto, anche per quanto riguarda le lettere maiuscole/minuscole.<br> Assicurati altresì di aver abilitato i cookies nel tuo browser.',
		'oLabel_header_success' => 'Login effettuato con successo',
		'oLabel_msg_success' => 'Hai effettuato il login con l\'utente \'###USER###\'',
		'oLabel_header_status' => 'Stato corrente',
		'oLabel_msg_status' => 'Questo è il tuo stato corrente:',
		'cookie_warning' => 'Attenzione: è probabilem che il tuo browser non abbia i cookies abilitati! Se il messaggio di avvenuto login scompare nella prossima pagina che visiterai, allora devi abilitare (o accettare) i cookies provenienti da questo sito.',
		'username' => 'Username:',
		'password' => 'Password:',
		'login' => 'Login',
		'logout' => 'Logout',
		'send_password' => 'Invia password:',
		'your_email' => 'Email:',
		'forgot_password' => 'Hai dimenticato la password?',
		'forgot_password_pswmsg' => 'La tua password 
Salve %s

Il tuo username è "%s"
La tua password è "%s"',
		'forgot_password_no_pswmsg' => 'La tua password
Salve %s

Nel nostro sistema non c\'è alcun utente con l\'indirizzo email da te indicato. Probabilmente hai specificato un email non corretto (controlla la maiuscole/minuscole), oppure non ti sei mai registrato.',
		'forgot_password_enterEmail' => 'Inserisci l\'indirizzo email che hai usato quanto hai registrato il tuo account.  La tua password sarà immediatamente inviata all\'indirizzo email che hai specificato. Controlla di aver scritto correttamente l\'indirizzo (anche per quanto riguarda le maiuscole/minuscole!)',
		'forgot_password_emailSent' => 'La tua password è stata inviata all\'indirizzo email %s',
		'forgot_password_backToLogin' => 'Ritorna al form di login',
	),
	'fr' => Array (
		'oLabel_header_welcome' => 'Identification de l\'utilisateur',
		'oLabel_msg_welcome' => 'Entrez votre nom d\'utilisateur et votre mot de passe pour vous identifier:',
		'oLabel_header_logout' => 'Vous êtes déconnecté',
		'oLabel_header_error' => 'Identification incorrect',
		'oLabel_header_success' => 'Identification correct',
		'oLabel_msg_success' => 'Vous êtes maintenant identifié en temps que \'###USER###\'',
		'oLabel_header_status' => 'Etat actuel',
		'oLabel_msg_status' => 'Votre état actuel:',
		'username' => 'Nom d\'utilisateur:',
		'password' => 'Mot de passe:',
		'login' => 'Identification',
		'logout' => 'Déconnexion',
		'send_password' => 'Envoyer le mot de passe',
		'your_email' => 'Votre adresse e-mail:',
		'forgot_password' => 'Oublier votre mot de passe?',
		'forgot_password_pswmsg' => 'Votre mot de passe
Hi %s

Votre nom d\'utilisateur est "%s"
Votre mot de passe est "%s"',
		'forgot_password_backToLogin' => 'Retourner au formulaire d\'identification',
	),
	'es' => Array (
		'oLabel_header_welcome' => 'Iniciar sesión de usuario',
		'oLabel_msg_welcome' => 'Introduzca su nombre de usuario y contraseña para iniciar su sesión en el sitio web:',
		'oLabel_header_logout' => 'Acaba de cerrar su sesión.',
		'oLabel_msg_logout' => 'Acaba de cerrar su sesión en este sitio web. Puede iniciar otra sesión usando el siguiente formulario.',
		'oLabel_header_error' => 'Falló el inicio de sesión',
		'oLabel_msg_error' => 'Ha ocurrido un error durante el inicio de sesión. Lo mas normal es que haya introducido un nombre de usuario o contraseña incorrectos.<br>Asegúrese de que los introduce de manera exacta, respetando las mayúsculas y minúsculas.<br>Otra posibilidad es que tenga las cookies deshabilitadas en su navegador.',
		'oLabel_header_success' => 'Inicio de sesión correcto',
		'oLabel_msg_success' => 'Ha iniciado sesión como \'###USER###\'',
		'oLabel_header_status' => 'Estado actual',
		'oLabel_msg_status' => 'Este es su estado actual:',
		'username' => 'Nombre de usuario:',
		'password' => 'Contraseña:',
		'login' => 'Iniciar sesión',
		'logout' => 'Finalizar sesión',
		'send_password' => 'Enviar contraseña',
		'your_email' => 'Su dirección de e-mail:',
		'forgot_password' => '¿Ha olvidado su contraseña?',
		'forgot_password_pswmsg' => 'Su contraseña
Hola %s

Su nombre de usuario es "%s"
Su contraseña es "%s"',
		'forgot_password_no_pswmsg' => 'Su contraseña
Hola %s

No podemos encontrar un nombre de usuario para esta dirección de correo, por lo tanto no podemos enviarle ninguna contraseña. Probablemente se haya confundido al introducir su dirección de correo (compruebe las mayúsculas y minúsculas), o es probable que aún no esté registrado en el sistema.',
		'forgot_password_enterEmail' => 'Por favor introduzca la dirección de correo electrónico con la que se registró. Después pulse el botón "Enviar contraseña" y le enviaremos la contraseña a su cuenta de correo. Asegúrese de escribir correctamente su dirección de correo.',
		'forgot_password_emailSent' => 'Su contraseña se ha enviado a la dirección de correo %s',
		'forgot_password_backToLogin' => 'Volver al formulario de inicio de sesión',
	),
	'nl' => Array (
		'oLabel_header_welcome' => 'Gebruikers Login',
		'oLabel_msg_welcome' => 'Voer uw gebruikersnaam en wachtwoord hier in om in te loggen op de website:',
		'oLabel_header_logout' => 'U bent uitgelogd.',
		'oLabel_msg_logout' => 'U bent zojuist uitgelogd van uw gebruikers-sessie op deze website. U kunt opnieuw inloggen of inloggen als een andere gebruiker d.m.v. onderstaand formulier.',
		'oLabel_header_error' => 'Login fout',
		'oLabel_msg_error' => 'Er is een fout opgetreden bij het inloggen. Waarschijnlijk heeft u uw gebruikersnaam of wachtwoord niet goed ingevoerd.<br>Zorg ervoor dat u die precies zo invoert als ze zijn, lettend op hoofdletters/kleine letters.<br>Ook kan het zijn dat cookies uitstaan in uw browser.',
		'oLabel_header_success' => 'Login succesvol',
		'oLabel_msg_success' => 'U bent nu ingelogd als \'###USER###\'',
		'oLabel_header_status' => 'Huidige status',
		'oLabel_msg_status' => 'Dit is uw huidige status:',
		'cookie_warning' => 'Waarschuwing: Het is mogelijk dat uw browser geen "cookies" accepteerd. Als uw aanmelding na doorklikken weer is verdwenen, zal dit vrijwel zeker het geval zijn. Om dit te herstellen moet u de mogelijkheid tot het gebruiken van cookies in uw browser activeren of voor deze site accepteren!',
		'username' => 'Gebruikersnaam:',
		'password' => 'Wachtwoord:',
		'login' => 'Inloggen',
		'logout' => 'Uitloggen',
		'send_password' => 'Wachtwoord verzenden',
		'your_email' => 'Uw email:',
		'forgot_password' => 'Wachtwoord vergeten?',
		'forgot_password_pswmsg' => 'Uw wachtwoord
Hallo %s

Uw wachtwoord is "%s"
Uw wachtwoord is "%s"',
		'forgot_password_no_pswmsg' => 'Uw wachtwoord
Hallo %s

We konden geen gebruikersnaam vinden met dit emailadres en kunnen u het wachtwoord niet toezenden. Waarschijnlijk heeft een een typefout gemaakt in het emailadres (hoofdletters / kleine letters maken verschil) of misschien bent u nog niet geregistreerd?',
		'forgot_password_enterEmail' => 'Geef a.u.b. het emailadres op waarmee u deze gebruikersaccount heeft geregistreerd. Klik vervolgens op "Wachtwoord verzenden" en u krijgt uw wachtwoord onmiddelijk toegestuurd. Zorg ervoor dat u geen typefouten maaakt in het emailadres.',
		'forgot_password_emailSent' => 'Uw wachtwoord is nu gestuurd naar het email adres %s',
		'forgot_password_backToLogin' => 'Terug naar het login formulier',
	),
	'cz' => Array (
	),
	'pl' => Array (
		'oLabel_header_welcome' => 'Formularz logowania',
		'oLabel_msg_welcome' => 'Podaj swój login i has³o aby siê zalogowaæ:',
		'oLabel_header_logout' => 'Wylogowa³e¶ siê.',
		'oLabel_msg_logout' => 'W³a¶nie zosta³e¶ wylogowany. Mo¿esz zalogowaæ siê ponownie lub jako inny u¿ytkownik, korzystaj±c z poni¿szego formularza.',
		'oLabel_header_error' => 'B³êdne logowanie',
		'oLabel_msg_error' => 'Podczas logowania nast±pi³ b³±d. Najczê¶ciej jest on spowodowany z³ym loginem lub has³em.<BR>Upewnij siê, ¿e poda³e¶ poprawnie dane, wielko¶æ liter.<BR>Inny powód to wy³±czone ciasteczka (cookies) w twojej przegl±darce.',
		'oLabel_header_success' => 'Udane logowanie',
		'oLabel_msg_success' => 'Jeste¶ zalogowany jako \'###USER###\'',
		'oLabel_header_status' => 'Bie¿±cy status',
		'oLabel_msg_status' => 'Twój bie¿±cy status:',
		'cookie_warning' => 'Uwaga: Prawdopodobnie twoja przegl±darka ma wy³±czon± obs³ugê ciasteczek! Je¶li twoj login znika przy nastêpnym klikniêciu, powiniene¶ w³±czyæ akceptowanie ciasteczek (przynajmniej pochodz±cych z tej witryny)!',
		'username' => 'U¿ytkownik:',
		'password' => 'Has³o:',
		'login' => 'Zaloguj siê',
		'logout' => 'Wyloguj siê',
		'send_password' => 'Wy¶lij has³o',
		'your_email' => 'Twój email:',
		'forgot_password' => 'Zapomnia³em has³o.',
		'forgot_password_pswmsg' => 'Twoje has³o
Witaj %s

Twój login to   "%s"
Twoje has³o to "%s"',
		'forgot_password_no_pswmsg' => 'Twoje has³o
Witaj %s

Nie mogli¶my znale¼æ u¿ytkownika o tym emailu. Byæ mo¿e ¼le poda³e¶ adres email (wielko¶æ liter ma znaczenie) albo po prostu nie ma cie w naszej bazie.',
		'forgot_password_enterEmail' => 'Podaj adres email taki, jak wpisa³e¶ podczas rejstrowania konta. Wci¶nij "Wy¶lij has³o", a twoje has³o zostanie do wys³ane. Upewnij siê, ¿e poda³e¶ prawid³owy email.',
		'forgot_password_emailSent' => 'Twoje has³o zosta³o wys³ane pod adres %s',
		'forgot_password_backToLogin' => 'Powrót do formularza logowania',
	),
	'si' => Array (
	),
	'fi' => Array (
		'oLabel_header_welcome' => 'Käyttäjän sisäänkirjoitus',
		'oLabel_msg_welcome' => 'Anna käyttäjätunnuksesi ja salasanasi kirjoittautuaksesi sisään sivuille:',
		'oLabel_header_logout' => 'Olet kirjoittautunut ulos.',
		'oLabel_msg_logout' => 'Kirjoittauduit ulos tämän sivuston käyttäjäsessiosta. Voit kirjoittautua uudestaan sisään allaolevalla lomakkeella.',
		'oLabel_header_error' => 'Sisäänkirjoittautumisessa virhe.',
		'oLabel_msg_error' => 'Sisäänkirjoittautumisessa tapahtui virhe. Todennäköisesti et antanut käyttäjätunnusta tai salasanaa oikein.<BR>Annoitko tiedot aivan oikein huomioiden isot ja pienet kirjaimet<BR>Toinen mahdollisuus on ettei selaimessasi ole cookies käytössä.',
		'oLabel_header_success' => 'Onnistunut sisäänkirjoittautuminen.',
		'oLabel_msg_success' => 'Olet kirjoittautunut sisään käyttäjätunnuksella \'###USER###\'',
		'oLabel_header_status' => 'Tila',
		'oLabel_msg_status' => 'Tämän hetkinen tilasi on:',
		'cookie_warning' => 'Varoitus: Selaimessasi eivät cookies ole ilmeisesti hyväksyttyjä! Jos login häviää seuraavalla klikkauksella Sinun tulee heti mahdollistaa cookies tälle websivustolle!',
		'username' => 'Käyttäjänimi:',
		'password' => 'Salasana:',
		'login' => 'Sisäänkirjoitus',
		'logout' => 'Uloskirjoitus',
		'send_password' => 'Lähetä salasana',
		'your_email' => 'Sähköpostiosoitteesi:',
		'forgot_password' => 'Unohditko salasanasi?',
		'forgot_password_pswmsg' => 'Salasanasi
Hi %s

Käyttäjätunnuksesi on "%s"
Salasanasi on "%s"',
		'forgot_password_no_pswmsg' => 'Salasanasi
Hi %s

Emme löytäneet käyttäjätunnusta tälle sähköpostiosoitteelle emmekä siksi voi lähettää Sinulle salasanaa. Olet mahdollisesti antanut virheellisen sähköpostiosoiteen (huomaa isojen ja pienten kirjainten ero) tai ehkä et ole vielä rekisteröitynyt?',
		'forgot_password_enterEmail' => 'Ole hyvä ja anna sähköpostiosoite jonka rekisteröit käyttäjätunnuksellesi. Paina sitten "Lähetä salasana" niin salasanasi lähetetään sähköpostilla välittömästi. Varmista että annat sähköpostiosoitteesi oikein.',
		'forgot_password_emailSent' => 'Salasanasi on nyt lähetetty sähköpostiosoitteeseen %s',
		'forgot_password_backToLogin' => 'Palaa sisäänkirjoitus lomakkeelle',
	),
	'tr' => Array (
	),
	'se' => Array (
		'oLabel_header_welcome' => 'Användarinloggning',
		'oLabel_msg_welcome' => 'Fyll i ditt användarnamn och lösenord för att komma in på websiten:',
		'oLabel_header_logout' => 'Du är utloggad',
		'oLabel_msg_logout' => 'Du har nyss loggat ut från din session på denna website. Du kan logga in på nytt eller byta identitet med nedanstående formulär.',
		'oLabel_header_error' => 'Inloggningen misslyckades',
		'oLabel_msg_error' => 'Ett fel uppstod vid inloggningen. Mest troligt är, att du inte skrev in användarnamnet och lösenordet rätt.<br>Försäkra dej om att du skrev dem exact så som de är, med gemena och versaler.<br>En annan möjlighet är att du förbjudit din dator att ta emot Cookies.',
		'oLabel_header_success' => 'Inloggningen lyckades',
		'oLabel_msg_success' => 'Du är inloggad med namnet \'###USER###\'',
		'oLabel_header_status' => 'Nuläge',
		'oLabel_msg_status' => 'Detta är din status för tillfället:',
		'username' => 'Användarnamn:',
		'password' => 'Lösenord:',
		'login' => 'Logga in',
		'logout' => 'Logga ut',
		'send_password' => 'Skicka lösenord',
		'your_email' => 'Din epostadress:',
		'forgot_password' => 'Har du glömt ditt lösenord?',
		'forgot_password_pswmsg' => 'Ditt lösenord
Hej %s

Ditt användarnamn är "%s"
Ditt lösenord är "%s"',
		'forgot_password_no_pswmsg' => 'Ditt lösenord
Hej %s

Vi kunde inte hitta ett användarnamn för detta lösenord så vi kan inte sända dej ditt lösenord. Det är möjligt att du skrivit din epostadress felaktigt (det är skillnad på små och stora bokstäver) eller så är du kanske inte ännu en registrerad användare?',
		'forgot_password_enterEmail' => 'Vänligen fyll i den epostadress som du använde när du registrerade dej. Klicka sedan på "Skicka lösenord" och ditt lösenord skickas omedelbart till din epostbox. Var noga med att skriva adressen rätt!',
		'forgot_password_emailSent' => 'Ditt lösenord har nu skickats till epostadressen %s',
		'forgot_password_backToLogin' => 'Gå tillbaka till inloggningssidan',
	),
	'pt' => Array (
		'oLabel_header_welcome' => 'Acesso do utilizador',
		'oLabel_msg_welcome' => 'Digite aqui seu nome de utilizador e senha para entrar no site:',
		'oLabel_header_logout' => 'Você desconectou-se do site.',
		'oLabel_msg_logout' => 'Você acaba de se desconectar. Você pode entrar novamente ou como outro utilizador através do formulário abaixo.',
		'oLabel_header_error' => 'Falha no Acesso.',
		'oLabel_msg_error' => 'Ocorreu um erro durante o Acesso. O mais comum é que você não digitou seu nome de utilizador/senha corretamente.<BR>Esteja certo de que os digitou precisamente como são, incluindo maiúsculas/minúsculas.<BR>Outra possibilidade é que seu navegador não aceita <i>cookies</i>.',
		'oLabel_header_success' => 'Acesso bem sucedido.',
		'oLabel_msg_success' => 'Você está agora conectado como \'###USER###\'',
		'oLabel_header_status' => 'Estado actual',
		'oLabel_msg_status' => 'Este é seu estado actual:',
		'username' => 'Utilizador:',
		'password' => 'Senha:',
		'login' => 'Acesso',
		'logout' => 'Sair',
		'send_password' => 'Enviar senha',
		'your_email' => 'Seu e-mail:',
		'forgot_password' => 'Esqueceu sua senha?',
		'forgot_password_pswmsg' => 'Sua Senha
Olá %s

Seu nome de utilizador é "%s"
Sua senha é "%s"',
		'forgot_password_no_pswmsg' => 'Sua Senha
Olá %s

Não podemos encontrar um nome de utilizador para este endereço de e-mail e assim não podemos enviar-lhe a senha. Provavelmente você não digitou corretamente o endereço de e-mail (letras maiúsculas e minúsculas fazem diferença) ou talvez você não se tenha registado ainda?',
		'forgot_password_enterEmail' => 'Por favor, digite o endereço de e-mail que você usou ao registar sua conta de utilizador. Então pressione "Enviar senha" e sua senha ser-lhe-á imediatamente enviada por e-mail. Esteja certo de informar corretamente seu endereço de e-mail.',
		'forgot_password_emailSent' => 'Sua senha foi agora enviada para o e-mail %s',
		'forgot_password_backToLogin' => 'Retornar ao formulário de acesso',
	),
	'ru' => Array (
		'oLabel_header_welcome' => 'Âõîä â ñèñòåìó',
		'oLabel_msg_welcome' => 'Ââåäèòå èìÿ ïîëüçîâàòåëÿ è ïàğîëü äëÿ âõîäà â ñèñòåìó:',
		'oLabel_header_logout' => 'Âû âûøëè èç ñèñòåìû.',
		'oLabel_msg_logout' => 'Âû àâòîìàòè÷åñêè âîøëè â ñèñòåìó. ×òîáû çàğåãèñòğèğîâàòüñÿ ñíîâà èëè âîéòè â ñèñòåìó ïîä äğóãèì èìåíåì, èñïîëüçóéòå ôîğìó íèæå.',
		'oLabel_header_error' => 'Îøèáêà ïğè âõîäå â ñèñòåìó',
		'oLabel_msg_error' => 'Ïğîèçîøëà îøèáêà ïğè âõîäå â ñèñòåìó. Âåğîÿòíî èìÿ ïîëüçîâàòåëÿ èëè ïàğîëü íå ñîîòâåòñòâóşò.<BR>Ïğîâåğüòå, ïğàâèëüíî ëè ââåäåíû äàííûå, áûë ëè èñïîëüçîâàí ïğàâèëüíûé ÿçûê, çàãëàâíûå èëè ïğîïèñíûå áóêâû.<BR>Óáåäèòåñü òàêæå, äîïóñêàåò ëè Âàø îáîçğåâàòåëü Cookies.',
		'oLabel_header_success' => 'Âõîä â ñèñòåìó ïğîøåë óñïåøíî',
		'oLabel_msg_success' => 'Âàøå èìÿ ïîëüçîâàòåëÿ: \'###USER###\'',
		'oLabel_header_status' => 'Òåêóùèé ñòàòóñ',
		'oLabel_msg_status' => 'Âàø òåêóùèé ñòàòóñ:',
		'cookie_warning' => 'Âíèìàíèå: âîçìîæíî Âàø îáîçğåâàòåëü íå äîïóñêàåò Cookies! Åñëè ôîğìà âõîäà íå îòîáğàçèòñÿ ïîñëå ñëåäóşùåãî ùåë÷êà, óñòàíîâèòå äîïóñê Cookies!',
		'username' => 'Èìÿ ïîëüçîâàòåëÿ:',
		'password' => 'Ïàğîëü:',
		'login' => 'Âõîä â ñèñòåìó',
		'logout' => 'Âûõîä èç ñèñòåìû',
		'send_password' => 'Îòïğàâèòü',
		'your_email' => 'Âàø àäğåñ:',
		'forgot_password' => 'Çàáûëè ïàğîëü?',
		'forgot_password_pswmsg' => 'Âàø ïàğîëü

Çäğàâñòâóéòå, %s.

Âàøå èìÿ ïîëüçîâàòåëÿ: "%s"
Âàø ïàğîëü: "%s"',
		'forgot_password_no_pswmsg' => 'Âàø ïàğîëü

Çäğàâñòâóéòå, %s.

Ïîä óêàçàííûì Âàìè àäğåñîì ıëåêòğîííîé ïî÷òû íå áûë íàéäåí ïîëüçîâàòåëü ñèñòåìû.
Â ñâÿçè ñ ıòèì ìû íå ìîæåì îòïğàâèòü Âàì Âàø ïàğîëü.
Ïğîâåğüòå ïîæàëóéñòà, óêàçàëè ëè Âû ïğàâèëüíûé àäğåñ (ñèñòåìà ğàçëè÷àåò çàãëàâíûå è ïğîïèñíûå áóêâû).
Âîçìîæíî òàêæå, ÷òî Âû åùå íå ïğîøëè ğåãèñòğàöèş.',
		'forgot_password_enterEmail' => 'Ââåäèòå, ïîæàëóéñòà, àäğåñ ıëåêòğîííîé ïî÷òû, óêàçàííûé Âàìè ïğè ğåãèñòğàöèè. Íàæìèòå íà "îòïğàâèòü", è Âû ïîëó÷èòå Âàø ïàğîëü íà äàííûé àäğåñ íåçàìåäëèòåëüíî. Óäîñòîâåğüòåñü â ïğàâèëüíîñòè óêàçàííîãî àäğåñà.',
		'forgot_password_emailSent' => 'Âàø ïàğîëü áûë âûñëàí ïî àäğåñó %s',
		'forgot_password_backToLogin' => 'Âåğíóòüñÿ ê ôîğìå âõîäà â ñèñòåìó',
	),
	'ro' => Array (
	),
	'ch' => Array (
	),
	'sk' => Array (
	),
	'lt' => Array (
	),
	'is' => Array (
	),
	'hr' => Array (
	),
	'hu' => Array (
	),
	'gl' => Array (
		'oLabel_header_welcome' => 'Atuisup isernera',
		'oLabel_msg_welcome' => 'Atuisutut taanerit isissullu uunga allaguk nittartakkamut iserniassagavit:',
		'oLabel_header_logout' => 'Anivutit.',
		'oLabel_msg_logout' => 'Nittartakkamiinnernit aneqqammerputit. Immersugassaq ataaniittoq atorlugu iseqqissinnaavutit atuisutulluunniit allatut isersinnaallutit.',
		'oLabel_header_error' => 'Iserneq iluatsinngitsoorpoq',
		'oLabel_msg_error' => 'Isernerata nalaani ajortoqarpoq. Ilimanarneruvoq atuisutut atit isissulluunniit allassimanngikkit.<BR>Eqqoqqissaartumik allanneqartut qulakkeeruk, naqqinnerit angissusii puiornagulu.<BR>Browserinni cookies atorunnaartinneqartut pisuutaasinnaapput aamma.',
		'oLabel_header_success' => 'Iserneq iluatsippoq',
		'oLabel_msg_success' => '\'###USER###\'-tut isersimavutit',
		'oLabel_header_status' => 'Massakkut inissisimaneq',
		'oLabel_msg_status' => 'Massakkut imatut inissisimavutit:',
		'cookie_warning' => 'Mianersuut: Browserinni cookies atuutigunanngillat! Tulliani toorsiguit login-erit tammassappat massakkorluinnaq cookies atuutissavatit! (nittartakkamilluunniit uannga cookies akuersaartarlugit)',
		'username' => 'Atuisup aqqa:',
		'password' => 'Isissut:',
		'login' => 'Iserneq',
		'logout' => 'Anineq',
		'send_password' => 'Isissut nassiuguk',
		'your_email' => 'Illit email-it:',
		'forgot_password' => 'Isissut puiorpiuk?',
		'forgot_password_pswmsg' => 'Illit isissutsit
Aluu %s

Atuisutut ima ateqarputit "%s"
Isissutsit imaappoq "%s"',
		'forgot_password_no_pswmsg' => 'Illit isissutsit
Aluu %s

Taamaattumik emaililimmik nalunaarsorneqartoq atuisup aqqa naninngilarput taamaattumillu isissut ilinnut nassiunneqarsinnaanngilaq.
e-mail adresse allannerlugunarpat (naqinnerit angissusii eqqamalaakkit) imaluuniit suli nalunaarsunngilatit?',
		'forgot_password_enterEmail' => 'email-it nalunaarsornermi atorsimasat uunga allaguk. Taava "Isissut nassiuguk" tooruk. Ingerlaannaq isissutissat nassiunneqarumaarpoq. Eqqortumik emailit allassimallugu qulakkeeruk.',
		'forgot_password_emailSent' => 'Isissutsit massakkut email-imut %s-imut nassiunneqarpoq',
		'forgot_password_backToLogin' => 'Isernermut immersugassamut uterit',
	),
	'th' => Array (
	),
	'gr' => Array (
	),
	'hk' => Array (
	),
	'eu' => Array (
	),
	'bg' => Array (
	),
	'br' => Array (
	),
	'et' => Array (
	),
	'ar' => Array (
	),
	'he' => Array (
	),
	'ua' => Array (
	),
);
?>
