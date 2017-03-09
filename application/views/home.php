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
<br />
<form action="" method="post">
Word:<input type="text" name="word" />
<br />
<input type="submit" value="Search.." name="search_word" />
</form>
<form action="" method="post">
Insert bulk words:
<br />
<textarea name="words"></textarea>
<br />
<input type="submit" value="Add words" name="add_words" />
</form>
<?php
echo $result;