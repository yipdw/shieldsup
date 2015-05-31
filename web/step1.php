<?php
require('conf.php');
require('lib.php');

    su_session_start();

    print_html_header();

?>
<form action="step2.php" method="POST">

<table>
<tr>
<td>Twitter username:</td>
<td><input type="text" name="username"></td>
<td><input type="submit" value="Submit"></td>
</tr>
</table>

</form>

</body>
</html>

