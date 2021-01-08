<?	// This will print the survey selected in .ht_report_print_survey
	// Start the session
	ini_set("session.gc_maxlifetime", "2592000");
	session_set_cookie_params(2592000);
	session_start();
	$page = $_GET['page']?$_GET['page']:'home';
	
	// Make sure that the .ht_CONFIG.php and .ht_website_objects.php files are
	// added only once per page.
	require_once('.ht_CONFIG.commented.php');
	require_once('.ht_CONFIG.php');
	require_once('.ht_Website_Objects.php');
	require_once('.ht_Survey_Classes.php');
	
	// If the user is logged out, make sure they only see the home, login,
	// and authorization pages.
	if (!$_SESSION['user']['is_loggedin'] &&
		$page!='home' &&
		$page!='login' &&
		$page !='auth')
		header("Location: ".$base_url.'/');
	
	// Instantiate the database object so we have it available for the page.
	$db = new DataBase($_CONFIG);
	
	// Semesters
	$sems[1] = "Spring ";
	$sems[2] = "Summer ";
	$sems[3] = "Fall ";
?>
<html>
	<head>
		<title>VCU Computer Science Surveys Site - Print a Survey</title>
		<style>
		body{
			font-family: Verdana, Arial, Helvetica, sans-serif;
			padding-bottom:20px;
		}
		.choice_text_area{
			width:320px;
			height:100px;
		}
		.question{
			margin:0px 0px 0px 10px;
			padding:0px 0px 15px 0px;
		}
		.question_num{
			font-weight:normal;
			font-size:12px;
			vertical-align:top;
			width:25px;
		}
		.question_text{
			font-weight:normal;
			font-size:12px;
			vertical-align:top;
			padding:0px;
			margin:0px;
		}
		.question_text_area{
			text-align:left;
			width:320px;
			height:100px;
		}
		.survey_name{
			font-weight:bold;
			font-size:18px;
			text-align:center;
			padding:0px 0px 10px 0px;
		}
		.survey_section{
			font-weight:bold;
			text-align:justify;
			font-size:16px;
			padding:0px 5px 10px 5px;
			border:none;
		}
		.td_spacer{
			font-size:1px;
			height:10px;
		}
		.td_text_area{
			text-align:center;
		}
		</style>
	</head>
	<body>
<?
	$suid = $_POST["survey"];
	$syid = $_POST["syid"];
	
	$query = <<< ENDOFQUERY
SELECT * FROM survey where survey_id=$suid
ENDOFQUERY;
	$survey = $db->getSELECT($query);
	$survey = $survey['results'][1];
	
	$query = <<< ENDOFQUERY
SELECT * FROM semester_year where semester_year_id=$syid
ENDOFQUERY;
	$term = $db->getSELECT($query);
	$term = $term['results'][1];
	
	// most of the following (until the end of this if) is copypasta from .ht_surveys
	// the inputs and "interactions" have been removed -- in fact, the entire form has been removed -- so no input is possible
	// some variables have different names and some variables have been removed
	// also the submit button returns the user to the selection page
	
	// Get survey, sections and questions for this particular survey.
	$query = <<< ENDOFQUERY
	SELECT DISTINCT a.survey_id AS su_id, a.survey_name, a.survey_text AS survey_text_id,
		e.text_id, e.survey_text, e.overflow_text AS su_text_overflow,
		b.*,
		f.section_id AS sec_id, f.section_text, f.overflow_text AS se_text_overflow,
		c.question_id AS qu_id, c.question_text, c.overflow_text AS qu_text_overflow,
		d.semester_year_id AS sy_id, d.semester, d.year
	FROM survey AS a, survey_question AS b, question AS c, semester_year AS d, survey_text AS e, section AS f
	WHERE a.survey_id=$suid
	AND e.text_id=a.survey_text
	AND a.survey_id=b.survey_id
	AND f.section_id=b.section_id
	AND c.question_id=b.question_id
	AND d.semester_year_id=$syid
	AND b.semester_year_id=d.semester_year_id
	ORDER BY b.question_order;
ENDOFQUERY;
	$su = $db->getSELECT($query);
	
	if (!is_array($su)) // the given survey doesn't exist with the given term
		echo "<b>Unable to find given survey for given semester.</b><p/>";
	else
	{
		$su = $su['results'];
		
		// A string that holds all the choice letters, instead of showing numbers.
		$chrs = "_abcdefghijklmnopqrstuvwxyz";
		
		// Get the choices pertaining to each question.
		$total = count($su);
		for($i=1; $i<=$total; $i++)
		{
			$query = <<< ENDOFQUERY
					SELECT DISTINCT a.*, b.choice_id
					AS ch_id, b.choice_text, b.overflow_text AS ch_text_overflow
					FROM question_choice AS a, choice AS b
					WHERE a.question_id={$su[$i]['qu_id']}
					AND a.choice_id=b.choice_id
					AND a.semester_year_id=$syid
					ORDER BY a.choice_order;
ENDOFQUERY;
			
			$ch = $db->getSELECT($query);
			$ch = $ch['results'];
			if (is_array($ch))
				$su[$i]['choices'] = $ch;
			else
				$su[$i]['choices'] = array();
		}
?>
	<div class="survey_name">
		<?=$su[1]['survey_name']?> (<?=$sems[$su[1]['semester']]?> <?=$su[1]['year']?>)
<?
		$currSec = -1;
		for($i=1; $i<=$total; $i++)
		{
			
			// This will output text for each section
			if($su[$i]['sec_id'] != $currSec)
			{
?>
	</div>
	<div class="survey_section">
<?
				$prevSec = $currSec;
				$currSec = $su[$i]['sec_id'];
				if (trim($su[$i]['section_text']) != '')
					echo('<b>'.$su[$i]['section_text'].'</b><br /><br />');
			}
			
			// Prints the question
			if($su[$i]['question_order'] > 0)
			{
				$this_q_id = 'qu_'.$su[$i]['qu_id'];
?>
		<div class="question">
			<table cellpadding="0" cellspacing="0" border="0" width="400">
				<tr class="question">
					<td class="question_num"><?= $su[$i]['question_order'] ?>.</td>
					<td class="question_text" colspan="2"><?= $su[$i]['question_text'] ?></td>
				</tr>
				<tr>
					<td colspan="3" class="td_spacer">&nbsp;</td>
<?
				// If this question has choices associated with it
				// The choices will be listed
				if (count($su[$i]['choices']) > 0)
				{
					$ch_ms = $su[$i]['choices'][1]['form_element']==4;
					
					$choice_count = count($su[$i]['choices']);
					for($j=1; $j<=$choice_count; $j++)
					{
						$this_choice = $su[$i]['choices'][$j];
?>
					<tr>
						<td>&nbsp;</td>
						<td class="question_num"><?= $chrs{$this_choice['choice_order']} ?>.</td>
						<td class="question_text">
							<font color="#0000FF"><?= $this_choice['choice_text'] ?></font></a>
<?
						// If this choice has a free response area to go along with it
						if ($this_choice['form_element'] == 11)
						{
?>
							<br/><br/>
							<textarea class="choice_text_area" disabled="disabled"></textarea>
							<br/><br/>
<?
						}
?>
						</td>
					</tr>
<?
					}
				}
				
				// If this question does not have any choices associated with it
				// A free response text area will be created
				else
				{
?>
					<tr>
						<td colspan="3" class="td_text_area">
							<textarea class="question_text_area" name="qu_<?= $su[$i]['qu_id'] ?>_text" disabled="disabled"></textarea>
							<br/>
						</td>
					</tr>
				</tr>
<?
				}
?>
			</table>
		</div>
<?
			}
		}
	}
?>
	</div>
	<form name="sr" method="post" action="index.php?page=print_survey">
		<input type="submit" name="return" value="Submit">
	</form>
	<br/>
	</body>
</html>