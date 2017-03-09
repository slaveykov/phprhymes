<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<style>
h4{
padding:0px;
margin:0px;
font-size:18px;
}
</style>
<form action="" method="post">
Insert one line lyric:
<br />
<textarea name="lyric" style="width:100%;"></textarea>
<br />
<input type="submit" value="Add lyric" name="add_lyric" />
</form>
<br />
<br />
<?php
echo $result;