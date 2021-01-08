<?
// Given a Survey, this report generator will generate a printable version of the survey.

// Semesters
$sems[1] = "Spring ";
$sems[2] = "Summer ";
$sems[3] = "Fall ";
?>
<h2 align=center>Print a Survey</h2>
<p> Select a Survey and Term, and the survey for that semester and year will be generated so it can be printed.<br/><br/>The submit button on the survey will bring you back to this page.<br/>It is only there to accurately reflect what the real survey would look like.</p>
<form name="sr" method="post" action="print_survey.php">
<?
// gets list of surveys in the database
$query = <<< ENDOFQUERY
	SELECT survey_id, survey_name FROM survey
	ORDER by survey_id
ENDOFQUERY;
$surveys = $db->getSELECT($query);

// gets list of terms values from database
$query = <<< ENDOFQUERY
	SELECT * FROM semester_year where semester!=0
	ORDER by year, semester
ENDOFQUERY;
$terms = $db->getSELECT($query);

// if there are no surveys....
if (!is_array($surveys))
{
?>
	</form>
	<font size=4><b><?=$name?>There are no surveys in the database.</b></font><p>
	<p><a href="?page=edsurv">Click Here </a> to navigate to the Add / Edit Surveys page.</p>
	<p><a href="?page=manrep">Click Here </a> to return to the Manage Reports page.</p><br/>
<?
	die;
}
// if there are no terms....
else if (!is_array($terms))
{
?>
	</form>
	<font size=4><b><?=$name?>There are no terms in the database.</b></font><p>
	<p>Would you like to go the the manage courses and terms page?</p>
	<form name=cform method=post action=?page=managecat>
	<input type="submit" name="cancel" value="Yes">
	</form>
	<form name=cform method=post action=?page=manrep>
	<input type="submit" name="cancel" value="No">
	</form>
<?
	die;
}
else
{
?>
	Survey:<br />
	<select name="survey">
<?
	// drop down menu for outcomes
	$surveys = $surveys['results'];
	$total = count($surveys);
	
	for ($i=1; $i<=$total; $i++)
	{
		$suid = $surveys[$i]['survey_id'];
		$name = $surveys[$i]['survey_name'];
?>
		<option value=<?=$suid?>><?=$name?></option>;
<?
	}
?>
	</select>
	
	<br/>
	<br/>
	
	Term:<br />
	<select name="syid">
<?
	// drop down menu for outcomes
	$terms = $terms['results'];
	$total = count($terms);
	
	for ($i=1; $i<=$total; $i++)
	{
		$syid = $terms[$i]['semester_year_id'];
		$term = $sems[$terms[$i]['semester']].' '.$terms[$i]['year'];
?>
		<option value=<?=$syid?>><?=$term?></option>;
<?
	}
?>
	</select>
<?
}
?>
<br/>
<br/>

<input type="submit" name="send" value="Submit">
</form>

<br/>

<form name="return" method="post" action="?page=manrep">
	<input type="submit" name="return" value="Cancel">
</form>