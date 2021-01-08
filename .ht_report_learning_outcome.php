<h2 align=center>Learning Outcome Report</h2>
<?
// Given a Learning Outcome and a Time Range, this report generator will report all assessments related to the Learning Outcome in the Time Range and report Learning Outcome Achievement Level for every term in the Time Range.

// The bar that determines whether the grades pass or fail
// The default is set to 80%, meaning if 80% or more are A/B/C/P then the Learning Outcome passes, otherwise it fails
$pass_bar = 80;

// Semesters
$sems[1] = "Spring ";
$sems[2] = "Summer ";
$sems[3] = "Fall ";

// After an outcome and time range has been submitted
if ($_POST && ($_POST['send'] == 'Submit'))
{
	$outid = $_POST["outcome"];
	$syid1 = $_POST["syid1"];
	$syid2 = $_POST["syid2"];
	$list = $_POST["list"];
	
	$query = <<< ENDOFQUERY
		SELECT * FROM outcome where outcome_id=$outid
ENDOFQUERY;
	$outcome = $db->getSELECT($query);
	$outcome = $outcome['results'][1]['outcome_text'];
	
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
	<h3 align=center><?=$outcome?> Report (<?=$semyear?>)</h3>
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
	$termcount = count($terms);
	
	////////////////////////////////////////////////////////////////////
	////////////////////////// GRADES SECTION //////////////////////////
	////////////////////////////////////////////////////////////////////
	if ($list == "grades" || $list == "both")
	{
?>
		<br/><h2>Grades Summary</h2>
<?
		// a REALLY nasty query that will get all classes that are linked to the given semester_year_ids
		$total = count($terms);
		$query = "SELECT * FROM vcu_class WHERE ";
		for ($i=1; $i<$total; $i++)
			$query = $query . "semester_year_id = " . $terms[$i]['semester_year_id'] . " OR ";
		$query = $query . "semester_year_id = " . $terms[$total]['semester_year_id'];
		
		$classes = $db->getSELECT($query);
		
		if (!is_array($classes))
		{
?>
			<p>There are no classes from <?=$semyear?>.</p>
<?
		}
		else
		{
			$classes = $classes['results'];
			$total = count($classes);
			
			// a REALLY nasty query that will get all classes that have the given outcome that are linked to the given semester_year_ids
			if ($total > 0)
				$query = "SELECT * FROM vcu_class_outcome WHERE outcome_id = " . $outid . " AND vcu_class_id = " . $classes[1]['vcu_class_id'];
			for ($i=2; $i<=$total; $i++)
				$query = $query . " OR outcome_id = " . $outid . " AND vcu_class_id = " . $classes[$i]['vcu_class_id'];
			
			$classouts = $db->getSELECT($query);
			
			if (!is_array($classouts))
			{
?>
				<p>No classes from <?=$semyear?> are assigned the <?=$outcome?> outcome.</p>
<?
			}
			else
			{
				$classouts = $classouts['results'];
				$total = count($classouts);
				
				// a REALLY nasty query that will get all LO grades for the outcomes from term 1 to term 2
				if ($total > 0)
					$query = "SELECT * FROM outcome_course_feedback WHERE vcu_class_outcome_id = " . $classouts[1]['vcu_class_outcome_id'];
				for ($i=2; $i<=$total; $i++)
					$query = $query . " OR vcu_class_outcome_id = " . $classouts[$i]['vcu_class_outcome_id'];
				
				$grades = $db->getSELECT($query);
				$grades = $grades['results'];
				$total = count($grades);
				$pass = $fail = 0;
				
				// totals the grades for the classes that are linked
				for ($i=1; $i<=$total; $i++)
				{
					$pass += $grades[$i]['achievement_A'];
					$pass += $grades[$i]['achievement_B'];
					$pass += $grades[$i]['achievement_C'];
					$fail += $grades[$i]['achievement_D'];
					$fail += $grades[$i]['achievement_F'];
					$pass += $grades[$i]['achievement_P'];
				}
				
				// if the total number of grades is 0
				if ($pass == 0 && $fail == 0)
				{
?>
					<p><?=$outcome?> has no grades from <?=$semyear?>.</p>
<?
				}
				// otherwise (if there are grades)
				else
				{
					$percent = 100 * $pass / ($pass + $fail);
?>
					<p><?=$outcome?> from <?=$semyear?> has 
<?
					if ($percent < $pass_bar)
					{
?>
						not
<?
					}
?>
					acheived a passing rating.</p>
<?
					// print pass rate
?>
					<p>Pass rate is <?=$percent?>%.</p>
<?
				}
			}
		}
	}
	
	////////////////////////////////////////////////////////////////////
	////////////////////// SURVEY ANSWERS SECTION //////////////////////
	////////////////////////////////////////////////////////////////////
	if ($list == "answers" || $list == "both")
	{
?>
		<br/><h2>Survey Answers Summary</h2>
<?
		// Build a query that will get all bars -- and all associated questions and choices -- that are linked to the given learning outcome
		$query = <<< ENDOFQUERY
			SELECT DISTINCT bar.bar_id, low_val, high_val,
				question.question_id, question.question_text,
				choice.choice_id, choice.choice_text, 
				choice_order
			FROM choice_bar, bar, question, choice, question_choice
			WHERE bar_meaning = $outid
			AND choice_bar.bar_id = bar.bar_id
			AND choice_bar.question_id = question.question_id
			AND choice_bar.question_id = question_choice.question_id
			AND choice_bar.choice_id = question_choice.choice_id
			AND choice_bar.choice_id = choice.choice_id
			ORDER BY question.question_id, bar.bar_id, choice_order
ENDOFQUERY;
		$bars = $db->getSELECT($query);
		$bars = $bars['results'];
		
		// if the given Learning Outcome has bar(s) associated with it...
		if (is_array($bars))
		{
			// make list of all questions to get answers from
			$barcount = count($bars);
			$questionscount = count($questions);
			$questions = array();
			$data = array();
			for ($i=1; $i<=$barcount; $i++)
			{
				$added = false;
				for ($j=1; $j<=$questionscount; $j++)
				{
					if ($bars[$i]['question_id'] == $questions[$j])
						$added = true;
				}
				
				if (!$added)
				{
					$questionscount++;
					$questions[$questionscount] = $bars[$i]['question_id'];
					//$data['question_id'][$questionscount] = $bars[$i]['question_id'];
					$data[$bars[$i]['question_id']]['total'] = 0;
				}
				
				$data[$bars[$i]['question_id']][$bars[$i]['choice_id']] = 0;
			}
			
			// Builds a query to get all responses / answers to the questions linked to the given learning outcome
			$query = <<< ENDOFQUERY
				SELECT choice_mc.response_id, question_id, choice_id, semester_year_id
				FROM choice_mc, response
				WHERE question_id = -1
ENDOFQUERY;
			for ($i=1; $i<=$questionscount; $i++)
				$query .= " OR choice_mc.response_id = response.response_id AND question_id = " . $questions[$i];
			$query .= " ORDER BY response_id, question_id, choice_id";
			$answers = $db->getSELECT($query);
			$answers = $answers['results'];
			
			// go thru answers
			// 1 entry is a single response_id
			//   there can be multiple responses per entry
			// total answer/entries and "passing" answers
			
			$total = count($answers);
			$rid = $qid = -1;
			for ($i=1; $i<=$total; $i++)
			{
				$syid = $answers[$i]['semester_year_id'];
				$in_time = false;
				// determines if this answer is within the specified time range
				for ($j=1; $j<=$termcount; $j++)
				{
					if ($syid == $terms[$j]['semester_year_id'])
						$in_time = true;
				}
				
				if ($in_time)
				{
					$prev_res = $rid;
					$prev_qid = $qid;
					$rid = $answers[$i]['response_id'];
					$qid = $answers[$i]['question_id'];
					$cid = $answers[$i]['choice_id'];
					
					$data[$qid][$cid]++;
					if ($prev_res != $rid || $prev_qid != $qid)
						$data[$qid]['total']++;
				}
			}
			
			// go thru bars, comparing bar_id and question_id for each new bar + question combination...
			$prev_bar = -1;
			$prev_q = -1;
			for ($i=1; $i<=$barcount; $i++)
			{
				// variables to compare previous bar and question
				// used to determine if a choice is added to the current question / bar
				$prev_bar = $bar_id;
				$prev_q = $q_id;
				$bar_id = $bars[$i]['bar_id'];
				$q_id = $bars[$i]['question_id'];
				
				// if this is a new bar / question combination
				if ($prev_bar != $bar_id || $prev_q != $q_id)
				{
					// if this is not the first "new" combination
					// only printed if changing from an "old" combination
					if ($i > 1)
					{
						// gets number of answers for bar
						$answers = $data[$bars[$i-1]['question_id']]['total'];
						if ($answers <= 0)
							$val = 0;
						else
							$val = 100 * $pass / $answers;
						
						echo "<br/><b>Bar " . $bars[$i-1]['bar_id'] . " Range:</b> (" . $bars[$i-1]['low_val'] . "-" . $bars[$i-1]['high_val'] . ")<br/>";
						echo "<b>Choices Tied To Bar: </b>" . $pass . "      " . "<b>Total Choices: </b> " . $answers . "<br/>";
						
						// only prints value and pass/fail if there are answers
						if ($answers > 0)
						{
							echo "<b>Bar Value: </b>" . $val . " %<br/>";
							
							// indicates if the bar has been met or not
							if (($val >= $bars[$i-1]['low_val']) && ($val <= $bars[$i-1]['high_val']))
								echo "<b>Bar Met </b><img src=i/check.jpg height=23>";
							else
								echo "<b>Bar Not Met </b><img src=i/x.jpg height=18>";
						}
						echo "<p/>";
					}
					
					$pass = 0;
					
					echo "<b>Question: </b>" . $bars[$i]['question_text'] . "<br/>";
					echo "<b>Choice(s): </b>" . $bars[$i]['choice_text'];
				}
				else
				{
					// output choice here
					echo " <b>; </b>" . $bars[$i]['choice_text'];
				}
				
				$pass += $data[$bars[$i]['question_id']][$bars[$i]['choice_id']];
			}
			
			// gets number of answers for bar
			$answers = $data[$bars[$i-1]['question_id']]['total'];
			if ($answers <= 0)
				$val = 0;
			else
				$val = 100 * $pass / $answers;
			
			echo "<br/><b>Bar " . $bars[$i-1]['bar_id'] . " Range:</b> (" . $bars[$i-1]['low_val'] . "-" . $bars[$i-1]['high_val'] . ")<br/>";
			echo "<b>Choices Tied To Bar: </b>" . $pass . "      " . "<b>Total Choices: </b> " . $answers . "<br/>";
			
			// only prints value and pass/fail if there are answers
			if ($answers > 0)
			{
				echo "<b>Bar Value: </b>" . $val . " %<br/>";
							
				// indicates if the bar has been met or not
				if (($val >= $bars[$i-1]['low_val']) && ($val <= $bars[$i-1]['high_val']))
					echo "<b>Bar Met </b><img src=i/check.jpg height=23>";
				else
					echo "<b>Bar Not Met </b><img src=i/x.jpg height=18>";
			}
		}
		
		// if the given Learning Outcome has no bar(s) associated with it
		else
		{
?>
			No questions are linked to <?=$outcome?>.<br/>
<?
		}
	}
?>
	<br/><br/>
	<form name="return" method="post" action="?page=alllos">
	<input type="submit" name="return" value="Return">
	</form>
<?
}

// Main page
else
{
?>
	<p> Given a Learning Outcome and Time Range, this report generator will list all assessments related to the given Learning Outcome in the Time Range and report Learning Outcome Achievement Level for the Time Range.</p>
	<form name="sr" method="post" action="?page=alllos">

<?
	// gets list of outcomes and time range values from database
	$query = <<< ENDOFQUERY
SELECT * FROM outcome
ENDOFQUERY;
	$outcomes = $db->getSELECT($query);
	
	$query = <<< ENDOFQUERY
SELECT * FROM semester_year where semester!=0
order by year, semester
ENDOFQUERY;
	$terms = $db->getSELECT($query);
	
	// if there are no outcomes....
	if (!is_array($outcomes))
	{
?>
		</form>
		<font size=4><b><?=$name?>There are no learning outcomes in the database.</b></font><p>
		<p>Would you like to go the the add / remove learning outcomes page?</p>
		<form name=cform method=post action=?page=addoutcomes>
		<input type="submit" name="cancel" value="Yes">
		</form>
		<form name=cform method=post action=?page=manrep>
		<input type="submit" name="cancel" value="No">
		</form>
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
	// give form for report generation
	else
	{
?>
		Learning Outcome:<br />
		<select name="outcome">
<?
		// drop down menu for outcomes
		$outcomes = $outcomes['results'];
		$total = count($outcomes);
		for ($i=1; $i<=$total; $i++)
		{
			$outid = $outcomes[$i]['outcome_id'];
			$out = $outcomes[$i]['outcome_text'];
?>
			<option value=<?=$outid?>><?=$out?></option>;
<?
		}
?>
		</select>
		
		<br/>
		<br/>
		
		Time Range:<br/>
		<select name="syid1">
<?
		// drop down menu for time range
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
	
	// radio input for GRADES / QUESTIONS
?>
	<br/>
	<br/><input type=radio name="list" value="grades"> List Grades Only
	<br/><input type=radio name="list" value="answers"> List Survey Answers Only
	<br/><input type=radio name="list" value="both" checked> List Both Grades and Survey Answers
	
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
