<h2 align=center>Survey Questions Report</h2>
<?
// Given a survey question that is not linked to any Learning Outcome and a Time Range, this report generator will generate all responses in the Time Range for the question.

// Semesters
$sems[1] = "Spring ";
$sems[2] = "Summer ";
$sems[3] = "Fall ";
?>

<?
if ($_POST && ($_POST['send'] == 'Submit'))
{
	$qid = $_POST['question'];
	$syid1 = $_POST["syid1"];
	$syid2 = $_POST["syid2"];
	
	$query = <<< ENDOFQUERY
	SELECT question_text from question where question_id = $qid
ENDOFQUERY;
	$question = $db->getSELECT($query);
	$question = $question['results'][1]['question_text'];
	
	$query = <<< ENDOFQUERY
SELECT * FROM semester_year where semester_year_id=$syid1
ENDOFQUERY;
	$term1 = $db->getSELECT($query);
	$term1 = $term1['results'][1];
	
	$query = <<< ENDOFQUERY
SELECT * FROM semester_year where semester_year_id=$syid2
ENDOFQUERY;
	$term2 = $db->getSELECT($query);
	$term2 = $term2['results'][1];
	
	// check to make sure terms are ordered correctly
	// if they are not ordered correctly, swap them
	if ($term1['year'] > $term2['year'] ||
		($term1['year'] == $term2['year'] &&
			$term1['semester'] > $term2['semester']))
	{
		$temp = $term1;
		$term1 = $term2;
		$term2 = $temp;
	}
	
	$year1 = $term1['year'];
	$year2 = $term2['year'];
	$sem1 = $term1['semester'];
	$sem2 = $term2['semester'];
	$semyear = $sems[$sem1].' '.$year1;
	if ($year1 != $year2 || $sem1 != $sem2)
		$semyear .= ' to '.$sems[$sem2].' '.$year2;
?>
	<h3 align=center><?=$semyear?></h3>
	<p><b><?=$question?></b></p><br/>
<?
	// query the database for all semester_years that are between term1 and term2, inclusive
	// if terms have the same year
	if ($year1 == $year2)
	{
		$query = <<< ENDOFQUERY
		SELECT * FROM semester_year
		where year = $year1 and semester != 0
		and semester >= $sem1 and semester <= $sem2
		order by year, semester
ENDOFQUERY;
	}
	// if terms have different years
	else
	{
		$query = <<< ENDOFQUERY
		SELECT * FROM semester_year
		where year > $year1 and year < $year2 and semester != 0
		or year = $year1 and semester >= $sem1 and semester != 0
		or year = $year2 and semester <= $sem2 and semester != 0
		order by year, semester
ENDOFQUERY;
	}
	$terms = $db->getSELECT($query);
	$terms = $terms['results'];
	$sems = count($terms);
	
	// Gets the multiple choice answers for the given question
	$query = <<< ENDOFQUERY
	SELECT choice.choice_text, multi_choice_answers.semester_year_id, multi_choice_answers.value
	FROM choice_mc, choice, multi_choice_answers
	WHERE question_id = $qid
	AND choice_mc.mc_id = multi_choice_answers.mc_id
	AND choice_mc.choice_id = choice.choice_id
	ORDER BY value
ENDOFQUERY;
	
	$mcans = $db->getSELECT($query);
	
	// Gets the open response answers for the given question
	$query = <<< ENDOFQUERY
	SELECT open_response.response_text, response.semester_year_id, semester_year.semester, semester_year.year
	FROM choice_oresponse, open_response, response, semester_year
	WHERE question_id = $qid
	AND choice_oresponse.oresponse_id = open_response.oresponse_id
	AND choice_oresponse.response_id = response.response_id
	AND response.semester_year_id = semester_year.semester_year_id
	ORDER BY year, semester, choice_oresponse.oresponse_id
ENDOFQUERY;
	
	$oans = $db->getSELECT($query);
	
	// If there are Multiple Choice Answers
	if (is_array($mcans))
	{
		$mcans = $mcans['results'];
		$total = count($mcans);
		
		// Total the answers
		$prevval = -1;
		$valcount = 0;
		for($i=1; $i<=$total; $i++)
		{
			// finds if this answer is in the time range given
			$add = false;
			for ($j=1; $j<=$sems; $j++)
				if ($mcans[$i]['semester_year_id'] == $terms[$j]['semester_year_id'])
				{
					$add = true;
					break;
				}
			
			// if this answer is in the time range given
			if ($add)
			{
				// this helps to build an associative array to link value to the choice that it represents
				$val = $mcans[$i]['value'];
				if ($val != $prevval)
				{
					$prevval = $val;
					$valcount++;
					$responses[$valcount] = $mcans[$i]['choice_text'];
				}
				
				$answers[$valcount]++;
			}
		}
		if ($valcount > 0)
		{
?>
			<h4>Multiple Choice Answers</h4>
<?
		}
		for ($i=1; $i<=$valcount; $i++)
		{
?>
			<p><b><?=$answers[$i]?></b> answered "<?=$responses[$i]?>."</p>
<?
		}
	}
	
	// If there are Free Response Answers
	if (is_array($oans))
	{
		$oans = $oans['results'];
		$total = count($oans);
		
		if ($total > 0)
		{
?>
			<h4>Open Response Answers</h4>
<?
		}
		for ($i=1; $i<=$total; $i++)
		{
			// finds if this answer is in the time range given
			$add = false;
			for ($j=1; $j<=$sems; $j++)
				if ($oans[$i]['semester_year_id'] == $terms[$j]['semester_year_id'])
				{
					$add = true;
					break;
				}
			
			// if this answer is in the time range given
			if ($add)
			{
?>
				<p><?=$oans[$i]['response_text']?></p>
<?
			}
		}
	}
?>
	<br/>
	<form name="return" method="post" action="?page=surveyqs">
	<input type="submit" name="return" value="Return">
	</form>
<?
}
else
{
?>
	<p>Given a survey question that is not linked to any Learning Outcome and a Time Range, this report generator will generate all responses in the Time Range for the question.</p>
	<form name="sr" method="post" action="?page=surveyqs">
	
<?
	// Gets all questions that are linked to a Learning Outcome
	$query = <<< ENDOFQUERY
	SELECT DISTINCT question.question_id
	FROM question, choice_bar
	WHERE question.question_id = choice_bar.question_id AND bar_meaning != 0
	ORDER BY question.question_id
ENDOFQUERY;
	$linked = $db->getSELECT($query);
	
	// Builds a query to get all questions not listed in $linked from question
	$query = "SELECT * FROM question";
	if (is_array($linked))
	{
		$linked = $linked['results'];
		$total = count($linked);
		
		$query .= " WHERE question_id != " . $linked[1]['question_id'];
		for ($i=2; $i<=$total; $i++)
			$query .= " AND question_id != " . $linked[$i]['question_id'];
	}
	
	$questions = $db->getSELECT($query);
	
	// Gets all terms from the database
	$query = <<< ENDOFQUERY
	SELECT * FROM semester_year where semester!=0
	order by year, semester
ENDOFQUERY;
	
	$terms = $db->getSELECT($query);
	
	if (!is_array($questions))
	{
?>
		</form>
		<font size=4><b><?=$name?>There are no non-linked questions in the database.</b></font><p>
		<p><a href="?page=edsurv">Click Here </a> to navigate to the Add / Edit Surveys page.</p>
		<p><a href="?page=manrep">Click Here </a> to return to the Manage Reports page.</p><br/>
<?
		die;
	}
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
	// drop down menu for the Learning Outcomes and time range
	else
	{
?>
		Question:<br />
		<select name="question">
<?
		$questions = $questions['results'];
		$total = count($questions);
		for ($i=1; $i<=$total; $i++)
		{
			$qid = $questions[$i]['question_id'];
			$q = substr($questions[$i]['question_text'], 0, 70);
			if ($q != $questions[$i]['question_text'])
				$q .= '...';
?>
			<option size=40 value=<?=$qid?>><?=$q?></option>;
<?
		}
?>
		</select>
		
		<br/>
		<br/>
		
		Time Range:<br/>
		<select name="syid1">
<?
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
		to
		<select name="syid2">
<?
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
<?
}
?>
