<?
// This page deals with adding and editing surveys.
// New survey types can be added. New questions can
// be added and inserted between existing questions.
// New surveys can be made copies of old surveys.
// Section headings can be changed as well. Questions
// with no choices are treated as open response questions.
// All learning outcomes can be added and deleted to questions.

// function used to check whether a variable is a number or not
function isNum($num) {

	for ($i = 0; substr($num, $i, 1) != ""; $i++) {
		$currLetter = substr($num, $i, 1);

		$Numbers = "1234567890";
		$isNum = false;
		for ($k = 0; ($k < 10) && !$isNum; $k++) {

			if (substr($Numbers,$k,1) == $currLetter) {
				$isNum = true;
			}
		}
		if (!$isNum) {
			return false;
		}
	}
	return true;
}


// gets year range
$query = <<< ENDOFQUERY
select min(year) as miny, max(year) as maxy from semester_year
ENDOFQUERY;
$years = $db->getSELECT($query);

$minyear = $years['results'][1]['miny'];
$maxyear = $years['results'][1]['maxy'];
?>

<Script language = JavaScript>

<? // makes sure that a section is not blank or invalid // ?>
function validate_section ( )
{
	var Secid = document.addsec.section.value;
	var Other = document.addsec.newsec.value;
	var Beg = document.addsec.secbeg.value;
	var End = document.addsec.secend.value;
	var Numqs = document.addsec.numquests.value;
 	var ValidChars = "0123456789";
   	var Char;

	Numqs = Numqs + 0;
	Beg = Beg + 0;
	End = End + 0;
  	for (i = 0; i < Beg.length; i++) { 
      	Char = Beg.charAt(i);

      	if (ValidChars.indexOf(Char) == -1) 
        {
			alert("Beginning question is invalid.");
			return false;
        }
	}
  	for (i = 0; i < End.length; i++) { 
      	Char = End.charAt(i);

      	if (ValidChars.indexOf(Char) == -1) 
        {
			alert("Ending question is invalid.");
			return false;
        }
	}
	if ((Other == "") && (Secid == "Other")) {
		alert("Other section field is empty.");
		return false;
	}
	else if ((Other != "") && (Secid != "Other")){
		alert("Did not select other in the select field.");
		return false;
	}
	if (Beg == "") {
		alert("Beginning question for section is empty.");
		return false;
	}
	if (End == "") {
		alert("Ending question for section is empty.");
		return false;
	}
	if (End > Numqs) {
		alert("End range exceeds number of questions.");
		return false;
	}
	if (Beg > End) {
		alert("Question range is invalid.");
		return false;
	}
	if ((Beg == 0) || (End == 0)) {
		alert("Question range is invalid.");
		return false;
	}

    return true;
}

function validate_question ( )
{
	var Other = document.addq.oq.value;
	var Qid = document.addq.qs.value;
	var QO = document.addq.count.value;
	var NumQs = document.addq.numquests.value;
 	var ValidChars = "0123456789";
   	var Char;

	NumQs++;

  	for (i = 0; i < O.length; i++) { 
      	Char = QO.charAt(i);

      	if (ValidChars.indexOf(Char) == -1) 
        {
			alert("Question order is invalid.");
			return false;
        }
	}
	if (QO == 0) {
		alert("Question order is invalid.");
		return false;
	}
	if ((Other == "") && (Qid == "Other")) {
		alert("Other question field is empty.");
		return false;
	}
	else if ((Other != "") && (Qid != "Other")){
		alert("Did not select other in the select field.");
		return false;
	}

	if (QO == "") {
		alert("Question order field is empty.");
		return false;
	}
	if (QO > NumQs) {
		alert("Question order exceeds number of questions in survey.");
		return false;
	}
    return true;
}

function validate_survey ( )
{
	var Y = document.changesurvey.year.value;
 	var ValidChars = "0123456789";
   	var Char;

	if (Y == "") {
		alert("Year is blank.");
		return false;
	}
  	for (i = 0; i < Y.length; i++) { 
      	Char = Y.charAt(i);

      	if (ValidChars.indexOf(Char) == -1) 
        {
			alert("Year is invalid.");
			return false;
        }
	}
	if (Y < <?=$minyear?>) {
		alert("Year is out of bounds.");
		return false;
	}
    return true;
}

function validate_surveytype ( )
{
	var survey = document.surveytype.surveyname.value;
	var text = document.surveytype.surveytext.value;
	if (survey == "") {
		alert("Survey name is blank.");
		return false;
	}
	if (text == "") {
		alert("Survey text is blank.");
		return false;
	}
    return true;
}

function validate_surveytext ( )
{
	var text = document.surveytext.changestext.value;
	if (text == "") {
		alert("Survey text is blank.");
		return false;
	}
    return true;
}

function validate_mewoutcome ( )
{
	var outcome = document.newoutcome.newoc.value;
	if (text == "") {
		alert("New learning outcome is blank.");
		return false;
	}
    return true;
}

</Script>
<h1 align=center>Add/Edit Survey</h1>
<?
$count = 1;
$sems[0] = "All Semesters";
$sems[1] = "Spring";
$sems[2] = "Summer";
$sems[3] = "Fall";	

$chletter[1] = "a";
$chletter[2] = "b";
$chletter[3] = "c";
$chletter[4] = "d";
$chletter[5] = "e";
$chletter[6] = "f";
$chletter[7] = "g";
$chletter[8] = "h";

// if adding a new survey type
if ($_POST && ($_POST['send'] == "Add Survey Type")) {

	// gets survey name and survey text
	$surveyname = $_POST['surveyname'];
	$surveytext = $_POST['surveytext'];

	// checks to see if survey already exists
	$query = <<< ENDOFQUERY
	select * from survey where survey_name='$surveyname'
ENDOFQUERY;

	$surveyresult = $db->getSELECT($query);

	// if survey name doesn't exist
	if (!is_array($surveyresult)){

		// checks to see if survey text already exists
		$query = <<< ENDOFQUERY
		select text_id from survey_text where survey_text='$surveytext'
ENDOFQUERY;

		$text = $db->getSELECT($query);

		if (!is_array($text)) {
			$db->insertRow(array('table'=>'survey_text', 'text_id'=>'', survey_text=>$surveytext, 'overflow_text'=>'j'));
			$text = $db->getSELECT($query);
		}
		$text = $text['results'][1]['text_id'];

		// inserts new survey type into survey table
		$db->insertRow(array('table'=>'survey', 'survey_id'=>'', survey_name=>$surveyname, 'survey_text'=>$text));
	}
}
else if ($_POST) {
	// gets survey id, year, and semester of current survey being edited
	$year = $_POST['year'];
	$sem = $_POST['sem'];
	$surveyid = $_POST['sid'];

	// gets semester_year_id
	$query = <<< ENDOFQUERY
	select semester_year_id from semester_year where year=$year and semester=$sem
ENDOFQUERY;

	$syid = $db->getSELECT($query);

	// if semester and year do not exist, inserts them into semester_year table
	if (!is_array($syid)) {
		$db->insertRow(array('table'=>'semester_year','semester_year_id'=>'','semester'=>$sem,'year'=>$year));
		$syid = $db->getSELECT($query);
	}
	$syid = $syid['results'][1]['semester_year_id'];

	// gets survey name		
	$query = <<< ENDOFQUERY
	select survey_name from survey where survey_id=$surveyid
ENDOFQUERY;
	$surveyname = $db->getSELECT($query);
	$surveyname = $surveyname['results'][1]['survey_name'];

	// writes survey, semester, and year
	echo "<font size=4>" . $sems[$sem] . " " . $year . " " . $surveyname . "</font><p>";

	// if changing the survey text	
	if ($_POST['send'] == "Change Survey Text") {
		$newstext = $_POST['changestext'];

		// searches for survey text to see if it exists
		$query = <<< ENDOFQUERY
		select text_id from survey_text where survey_text='$newstext'
ENDOFQUERY;

		$text = $db->getSELECT($query);

		// if survey text doesn't exist, inserts it into survey_text table
		if (!is_array($text)) {
			$db->insertRow(array('table'=>'survey_text', 'text_id'=>'', survey_text=>$newstext, 'overflow_text'=>'j'));
			$text = $db->getSELECT($query);
		}
		$text = $text['results'][1]['text_id'];

		// updates survey text of survey
		$query = <<< ENDOFQUERY
		update survey set survey_text=$text where survey_id=$surveyid
ENDOFQUERY;
		mysql_query($query);
	}
	// if adding a question to a survey
	else if ($_POST['send'] == "Add Question") {
		$sec = $_POST['section'];		// gets section id of question
		$count = $_POST['count'];		// gets question order of question
		$qid = $_POST['qs'];			// gets question id

		$AddChoices = true;			// add existing choices to question
		$DontAddQ = false;			// don't add question
		// if a new question needs to be added
		if ($qid == "Other") {
			$qt = $_POST['oq'];			// gets question text of new question

			// checks to see if question already exists
			$query = <<< ENDOFQUERY
			select * from question where question_text='$qt'
ENDOFQUERY;
			$qid = $db->getSELECT($query);
	
			// if it doesn't exist, inserts into the question table
			if (!is_array($result)) {
				$AddChoices = false;		// sets to false so not to look for any choices to tie to question
				$db->insertRow(array('table'=>'question', 'question_id'=>'', 'question_text'=>$qt, 'overflow_text'=>'j'));
				$qid = $db->getSELECT($query);
				$qid = $qid['results'][1]['question_id'];

				$cat = $_POST['cat'];
				if ($cat == 0) {
					$db->insertRow(array('table'=>'noncat_question', 'question_id'=>$qid));
				}
				$corder = 1;		// choice order

				// searches for choices to the question
				foreach($_POST as $key => $value) {
					// if it is a choice
					if((substr($key, 0, 2) == 'ch') && ($value != "")) {

						$form = $_POST['form'];		// form element for multiple choice, choice response, multiselect
						// searches for the form element value of the choice
						foreach($_POST as $key2 => $value2) {
							if(substr($key2, 0, 2) == 'el') {

								if (substr($key2, 2, 1) == substr($key,2,1)) {
									$form = 11;
								}
							}
						}
						// checks to see if choice already exists
						$query = <<< ENDOFQUERY
						select choice_id from choice where choice_text='$value'
ENDOFQUERY;

						$chid = $db->getSELECT($query);

						// if choice doesn't exist, inserts it into the choice table
						if (!is_array($chid)) {
							$db->insertRow(array('table'=>'choice', 'choice_id'=>'', 'choice_text'=>$value, 'overflow_text'=>'j'));
							$chid = $db->getSELECT($query);
						}
						$chid = $chid['results'][1]['choice_id'];
						// inserts choice and question into the question_choice table
						$db->insertRow(array('table'=>'question_choice', 'question_id'=>$qid, 'choice_id'=>$chid, 'choice_order'=>$corder, 'semester_year_id'=>$syid, 'form_element'=>$form));
						$corder++;		// increments choice order
					}
				}
			}
			else {
				$qid = $qid['results'][1]['question_id'];
			}
		}

		// if question already exists in question table
		if ($AddChoices) {

			// checks to see if question already exists in survey
			$query = <<< ENDOFQUERY
			select * from survey_question where question_id=$qid and survey_id=$surveyid and semester_year_id=$syid
ENDOFQUERY;
			$existq = $db->getSELECT($query);

			// if question isn't in survey, searches for the most recent existing survey that has that question
			// and finds existing choices and inserts them into the question_choice table
			if (!is_array($existq)) {
				$query = <<< ENDOFQUERY
				select distinct semester_year.semester_year_id, semester, year from semester_year, survey_question where
				survey_id=$surveyid and question_id=$qid and survey_question.semester_year_id=semester_year.semester_year_id
				order by year, semester
ENDOFQUERY;
				$semester_year = $db->getSELECT($query);
				$semester_year = $semester_year['results'];
				$last = count($semester_year);

				$semester_year = $semester_year[$last]['semester_year_id'];

				$query = <<< ENDOFQUERY
				select choice_id, form_element, choice_order from question_choice where question_id=$qid and semester_year_id=$semester_year
				order by choice_order
ENDOFQUERY;
				$choices = $db->getSELECT($query);
				if (is_array($choices)) {
					$choices = $choices['results'];
					for ($i = 1; $i <= count($choices); $i++) {
						$cid = $choices[$i]['choice_id'];
						$form = $choices[$i]['form_element'];
						$db->insertRow(array('table'=>'question_choice','semester_year_id'=>$syid,'question_id'=>$qid, 'choice_id' => $cid, 'form_element'=>$form, 'choice_order' => $i));
					}
				}
			}
			// if question is already in survey, makes sure that question isn't added again
			else {
				$DontAddQ = true;
			}
		}
		// if question is to be added to survey
		if (!$DontAddQ) {
			// updates question order of questions that come after the new question
			$query = <<< ENDOFQUERY
			update survey_question set question_order=question_order+1 where survey_id=$surveyid and semester_year_id=$syid and question_order >= $count
ENDOFQUERY;
			mysql_query($query);

			// inserts question and survey into survey_question table
			$db->insertRow(array('table'=>'survey_question','survey_id'=>$surveyid,'question_id'=>$qid, 'semester_year_id'=> $syid, 'question_order'=> $count, 'section_id'=>$sec));
			$count++;
		}

	}
	// if updating the categorization of a question
	// if question doesn't have to be categorized, adds it to the list of noncategorized questions
	// else deletes it from the list of noncategorized questions
	else if ($_POST['send'] == "Update Categorization") {
		$qid = $_POST['qid'];
		if ($_POST['cat'] == 1) {

			$query = <<< ENDOFQUERY
			delete from noncat_question where question_id=$qid
ENDOFQUERY;
			mysql_query($query);
		}
		else {
			$db->insertRow(array('table'=>'noncat_question','question_id'=>$qid));
		}
	}
	// if adding a section to the survey
	else if ($_POST['send'] == "Add Section") {
		$secid = $_POST['section'];		// gets section id of section
		$beg = $_POST['secbeg'];		// where section should start
		$end = $_POST['secend'];		// where section should end

		// if a new section heading is being added
		if ($secid == "Other") {
			$sectext = $_POST['newsec'];		// gets section heading text

			// inserts section into section table
			$db->insertRow(array('table'=>'section','section_id' => '', 'section_text' => $sectext, 'overflow_text'=>'j'));

			// gets section id of section
			$query = <<< ENDOFQUERY
			select section_id from section where section_text='$sectext'
ENDOFQUERY;
			$secid = $db->getSELECT($query);
			$secid = $secid['results'][1]['section_id'];
		}
		// sets section IDs of questions in survey to new section
		$query = <<< ENDOFQUERY
		update survey_question set section_id=$secid where survey_id=$surveyid and semester_year_id=$syid and question_order between $beg and $end
ENDOFQUERY;
		mysql_query($query);

	}
	// if adding a learning outcome
	else if ($_POST['send'] == "Add Outcome") {
		$qid = $_POST['qid'];					// question id tied to learning outcome
		$lowval = $_POST['low'];				// low range of bar
		$highval = $_POST['high'];				// high range of bar
		$outcome = $_POST['addoutcome'];		// learning outcome
		$ochoices = $_POST['outchoices'];		// choices in question tied to outcome

		if ( ( !isNum($lowval) || !isNum($highval) ) || ($lowval >= $highval)) {
			echo "Range is invalid.<p>";
		}
		else {
			// checks to see if bar range already exists
			$query = <<< ENDOFQUERY
			select bar_id from bar where low_val=$lowval and high_val=$highval
ENDOFQUERY;

			$barid = $db->getSELECT($query);

			// if bar range doesn't exist, inserts bar range into bar table
			if (!is_array($barid)) {
				$db->insertRow(array('table'=>'bar','bar_id'=>'','low_val'=>$lowval, 'high_val' => $highval, 'operator_1'=>'<', 'operator_2' => '<'));
				$barid = $db->getSELECT($query);
			}
			$barid = $barid['results'][1]['bar_id'];

			// checks to see if learning outcome tied to question already exists
			$query = <<< ENDOFQUERY
			select * from choice_bar where question_id=$qid and bar_id=$barid and bar_meaning=$outcome
ENDOFQUERY;

			$existoutcome = $db->getSELECT($query);

			// if it doesn't exist
			if (!is_array($existoutcome)) {

				$idx = 0;
				$c = 1;
				$outchoices = array();

				// extracts choices tied to bar
				while (substr($ochoices,$idx,1) != "") {
					for ($i = 1; $i <= count($chletter); $i++) {
						if ($chletter[$i] == substr($ochoices, $idx, 1)) {
							$outchoices[$c] = $i;
							$c++;
						}
					}
					$idx++;
				}

				// if there are choices tied to the bar
				if (count($outchoices) > 0) {

					// inserts choices into choice_bar table
					for ($i = 1; $i <= count($outchoices); $i++) {
						$query = <<< ENDOFQUERY
						select choice_id from question_choice where question_id=$qid and semester_year_id=$syid
						and choice_order=$outchoices[$i]
ENDOFQUERY;
						$qchoices = $db->getSELECT($query);
						$qchoices = $qchoices['results'];
						$choiceid = $qchoices[1]['choice_id'];
						$db->insertRow(array('table'=>'choice_bar','choice_id'=>$choiceid,'bar_id'=>$barid, 'question_id' => $qid, 'bar_meaning'=>$outcome));
					}
				}
			}
		}
	}
	// if deleting a choice from a question
	else if ($_POST['send'] == "Delete Choice(s)") {
		$qid = $_POST['qid'];		// question id	

		// searches for choices to be deleted and deletes them
		foreach($_POST as $key => $value) {
			if(substr($key, 0, 2) == 'dc') {

				$query = <<< ENDOFQUERY
				delete from question_choice where question_id=$qid and choice_order=$value and semester_year_id=$syid
ENDOFQUERY;
				mysql_query($query);
			}
		}

		// updates choice order of remaining choices
		$query = <<< ENDOFQUERY
		select choice_order from question_choice where question_id=$qid and semester_year_id=$syid
		order by choice_order
ENDOFQUERY;
		$chs = $db->getSELECT($query);
		if (is_array($chs)) {
			$chs = $chs['results'];

			for ($i = 1; $i <= count($chs); $i++) {
				$chorder = $chs[$i]['choice_order'];
				$query = <<< ENDOFQUERY
				update question_choice set choice_order=$i where question_id=$qid and choice_order=$chorder and semester_year_id=$syid
ENDOFQUERY;
				mysql_query($query);
			}
		}		
	}

	// if deleting an outcome
	else if ($_POST['send'] == "Delete Outcome") {
		$qid = $_POST['qid'];			// question id
		$barid = $_POST['barid'];		// bar range
		$out = $_POST['out'];			// learning outcome

		// deletes all choices in choice_bar tied to outcome
		$query = <<< ENDOFQUERY
		delete from choice_bar where question_id=$qid and bar_id=$barid and bar_meaning=$out
ENDOFQUERY;
		mysql_query($query);
	}

	// if adding a choice to a question
	else if (substr($_POST['send'],0,10) == "Add Choice") {

		$qid = $_POST['qid'];				// question id
		$chorder = $_POST['choiceorder'];	// choice order of new choice
		$chname = $_POST['choicename'];		// choice
		$form = "";				// type of choice

	
		// checks to see what kind of choice the new choice is
		foreach($_POST as $key => $value) {
			if( (substr($key, 0, 2) == 'el') && ($form == "")) {
				$form = $value;
			}
			else if(substr($key, 0, 2) == 'cr') {
				$form = 11;
			}
		}

		// if form element is still blank, means that the question already had choices and the new choice isn't a choice response
		// sets form element as the same as the first choice in the question
		if ($form == "") {

			$query = <<< ENDOFQUERY
			select form_element from question_choice where question_id=$qid and semester_year_id=$syid and choice_order=1
ENDOFQUERY;
			$form = $db->getSELECT($query);
			$form = $form['results'][1]['form_element'];
		}
		// gets total number of existing choices in question
		$query = <<< ENDOFQUERY
		select count(*) as numchoices from question_choice where question_id=$qid and semester_year_id=$syid
ENDOFQUERY;

		$numchoices = $db->getSELECT($query);
		$numchoices = $numchoices['results'][1]['numchoices'];

		if (!isNum($chorder)) {
			$chorder = $chorder = $numchoices + 1;
		}
		// if choice order of new choice is invalid, sets choice order to be last choice
		if (($chorder > ($numchoices + 1)) || ($chorder < 1)) {
			$chorder = $numchoices + 1;
		}

		// updates choice order of existing choices that come after new choice
		$query = <<< ENDOFQUERY
		update question_choice set choice_order=choice_order+1 where question_id=$qid and choice_order >= $chorder and semester_year_id=$syid
ENDOFQUERY;
		mysql_query($query);

		// makes sure choice doesn't already exist in choice table
		$query = <<< ENDOFQUERY
		select choice_id from choice where choice_text='$chname'
ENDOFQUERY;

		$chid = $db->getSELECT($query);

		// if choice doesn't exist, inserts it into the choice table
		if (!is_array($chid)) {
			$db->insertRow(array('table'=>'choice', 'choice_id'=>'', 'choice_text'=>$chname, 'overflow_text'=>'j'));
			$chid = $db->getSELECT($query);
		}

		// inserts new choice into question_choice table
		$chid = $chid['results'][1]['choice_id'];
		$db->insertRow(array('table'=>'question_choice','semester_year_id'=>$syid,'question_id'=>$qid, 'choice_id' => $chid, 'form_element'=>$form, 'choice_order' => $chorder));

	}
	// if deleting a question
	else if ($_POST['send'] == "Delete Question") {
		$qid = $_POST['qid'];		// question id of question to be deleted

		// deletes all choices from question
		$query = <<< ENDOFQUERY
		delete from question_choice where question_id=$qid and semester_year_id=$syid
ENDOFQUERY;
		mysql_query($query);

		// deletes question
		$query = <<< ENDOFQUERY
		delete from survey_question where question_id=$qid and semester_year_id=$syid and survey_id=$surveyid
ENDOFQUERY;
		mysql_query($query);
	

		// updates question order of remaining questions
		$query = <<< ENDOFQUERY
		select question_id, question_order from survey_question where semester_year_id=$syid and survey_id=$surveyid
		order by question_order
ENDOFQUERY;

		$newqs = $db->getSELECT($query);

		if (is_array($newqs)) {
			$newqs = $newqs['results'];

			for ($i = 1; $i <= count($newqs); $i++) {
				$qid = $newqs[$i]['question_id'];
				$query = <<< ENDOFQUERY
				update survey_question set question_order=$i where question_id=$qid and survey_id=$surveyid and semester_year_id=$syid
ENDOFQUERY;
				mysql_query($query);
			}
		}
	}

	// if deleting all questions, deletes all questions from the survey
	else if ($_POST['send'] == "Delete All") {
		$query = <<< ENDOFQUERY
		select question_id from survey_question where survey_id=$surveyid and semester_year_id=$syid
ENDOFQUERY;
		$quests = $db->getSELECT($query);
		if (is_array($quests)) {
			$quests = $quests['results'];
			for ($i = 1; $i <= count($quests); $i++) {
				$qid = $quests[$i]['question_id'];
				$query = <<< ENDOFQUERY
				delete from question_choice where question_id=$qid and semester_year_id=$syid
ENDOFQUERY;
				mysql_query($query);
			}
		}
		$query = <<< ENDOFQUERY
		delete from survey_question where survey_id=$surveyid and semester_year_id=$syid
ENDOFQUERY;
		mysql_query($query);
	}
	// if adding a new learning outcome type
	else if ($_POST['send'] == "Add New Outcome") {
		$outcome = $_POST['newoc'];
		$db->insertRow(array('table'=> 'outcome','outcome_id'=>'','outcome_text'=>$outcome));
	}
	// if loading questions of an existing survey into a new one
	else if ($_POST['send'] == "Load") {

		// gets all question ids of questions in survey
		$query = <<< ENDOFQUERY
		select question_id from survey_question where survey_id=$surveyid and semester_year_id=$syid
ENDOFQUERY;
		$quests = $db->getSELECT($query);

		// deletes all choices in questions
		if (is_array($quests)) {
			$quests = $quests['results'];
			for ($i = 1; $i <= count($quests); $i++) {
				$qid = $quests[$i]['question_id'];
				$query = <<< ENDOFQUERY
				delete from question_choice where question_id=$qid and semester_year_id=$syid
ENDOFQUERY;
				mysql_query($query);
			}
		}
		// empties survey
		$query = <<< ENDOFQUERY
		delete from survey_question where survey_id=$surveyid and semester_year_id=$syid
ENDOFQUERY;
		mysql_query($query);

		// gets all questions of existing survey to be loaded
		$existsyid = $_POST['semesteryear'];
		$query = <<< ENDOFQUERY
		select survey_id, question_id, question_order, section_id from survey_question where survey_id=$surveyid and
		semester_year_id=$existsyid
ENDOFQUERY;

		$re = $db->getSELECT($query);
		$re = $re['results'];

		// inserts questions into new survey
		for ($i=1; $i <= count($re); $i++) {
			$q = $re[$i]['question_id'];
			$qo = $re[$i]['question_order'];
			$sec = $re[$i]['section_id'];
			$db->insertRow( array('table'=> 'survey_question','survey_id'=>$surveyid,'question_id'=>$q,'semester_year_id'=>$syid,'question_order'=>$qo,'section_id'=>$sec));
		}

		// gets choices of all questions
		$query = <<< ENDOFQUERY
		select question_choice.question_id, choice_id, choice_order, form_element from question_choice, survey_question where survey_id=$surveyid and
		survey_question.question_id=question_choice.question_id and
		question_choice.semester_year_id=$existsyid
ENDOFQUERY;

		$re = $db->getSELECT($query);

		// inserts choices into questions
		if (is_array($re)) {

			$re = $re['results'];

			for ($i=1; $i <= count($re); $i++) {
				$q = $re[$i]['question_id'];
				$ch = $re[$i]['choice_id'];
				$co = $re[$i]['choice_order'];
				$f = $re[$i]['form_element'];
				$db->insertRow( array('table'=> 'question_choice','question_id'=>$q,'semester_year_id'=>$syid,'choice_order'=>$co,'form_element'=>$f,'choice_id'=>$ch));
			}
		}

	}

	// gets survey text of survey
	$query = <<< ENDOFQUERY
	select survey_text.survey_text from survey, survey_text where survey_id=$surveyid and survey_text.text_id=survey.survey_text
ENDOFQUERY;
	
	$surveytext = $db->getSELECT($query);
	$surveytext = $surveytext['results'][1]['survey_text'];

	// displays survey text and form for changing survey text
?>
	<table border=1 width=500><tr><td>
	<b><font size=4>Survey Text: </font></b><p>
	<?=$surveytext?><p>
	<form name=surveytext method=post action=?page=edsurv onSubmit="return validate_surveytext();">
	Change Survey Text: <br>
	<textarea name=changestext></textarea><p>
	<input type=hidden name=sem value=<?=$sem?>>
	<input type=hidden name=year value=<?=$year?>>
	<input type=hidden name=sid value=<?=$surveyid?>>
	<input type=submit name=send value="Change Survey Text">
	</form></td></tr></table><p>
<?

	// gets all existing surveys listed by semester and year, so we can load an existing survey into a new one
	$query = <<< ENDOFQUERY
	select distinct semester_year.semester_year_id, semester, year from semester_year, survey_question where
	survey_id=$surveyid and survey_question.semester_year_id=semester_year.semester_year_id
	order by year, semester
ENDOFQUERY;

	$existsurveys = $db->getSELECT($query);
	// if there are existing surveys, displays them and form for loading existing surveys
	if (is_array($existsurveys)) {
		$existsurveys = $existsurveys['results'];
?>
		<form method=post action=?page=edsurv>
		<input type=hidden name=sem value=<?=$sem?>>
		<input type=hidden name=year value=<?=$year?>>
		<input type=hidden name=sid value=<?=$surveyid?>>
		<select name=semesteryear>
<?
		for ($i = 1; $i <= count($existsurveys); $i++) {
			$sy = $existsurveys[$i]['semester_year_id'];
			$s = $existsurveys[$i]['semester'];
			$s = $sems[$s];
			$y = $existsurveys[$i]['year'];		

?>
			<option value=<?=$sy?>><?=$s?> <?=$y?></option>
<?
		}
?>
		</select>
		 <input type=submit name=send value="Load">
		</form>
		<p>
<?
	}
	// displays for adding a question
?>
	<table border=1><tr><td>
	<font size=4><b>Add Question</b></font><p>
	<form method=post name=addq action=?page=edsurv onSubmit="return validate_question ( );">
	<input type=hidden name=sem value=<?=$sem?>>
	<input type=hidden name=year value=<?=$year?>>
	<input type=hidden name=sid value=<?=$surveyid?>>
	Question Order: <input type=text size=2 name=count value=<?=$count?>><p>	<? // question order // ?>
	Question: <select name=qs>
<?

	// gets number of questions in current survey	
	$query = <<< ENDOFQUERY
	select count(*) as numqs from survey_question where survey_id=$surveyid and semester_year_id=$syid
ENDOFQUERY;

	$numqs = $db->getSELECT($query);
	$numqs = $numqs['results'][1]['numqs'];

	// gets all existing questions from previous surveys
	$query = <<< ENDOFQUERY
	select distinct question.question_id, question_text from question, survey_question where question.question_id = survey_question.question_id
	and survey_id=$surveyid
ENDOFQUERY;
	$qs = $db->getSELECT($query);

	// makes a drop down list of all questions from previous surveys
	if (is_array($qs)) {
		$qs = $qs['results'];

		for ($i = 1; $i <= count($qs); $i++) {
			$currq = $qs[$i]['question_text'];
			$currq = substr($currq, 0, 60);
			$currqid = $qs[$i]['question_id'];
			echo "<option value=" . $currqid . ">" . $currq . "</option>";
		}
	}
?>
	<option value=Other>Other</option>
	</select><p>

	<input type=hidden name=numquests value=<?=$numqs?>>
	Other: <input type=text size=60 name=oq><p>
	Categorize:
	<input type=radio name="cat" value=0 checked>No</radio>
	<input type=radio name="cat" value=1>Yes</radio><p>
<?  ///// whether question is multiple choice or multiselect ///// ?>
	<input type=radio name="form" value=3 checked>Multiple Choice</radio>
	<input type=radio name="form" value=4>MultiSelect</radio><p>
<?	// creates input text for 8 choices for a new questions // ?>
	Choices:<br>
<?
	for ($i = 1; $i <= 8; $i++) {
		$chname = "ch" . $i;
		$element = "el" . $i;
		echo $chletter[$i] . ". ";
?>
		<input type=text size=40 name=<?=$chname?>><br>

		<? // whether choice is open response or not // ?>
		<input type=checkbox name=<?=$element?> value=<?=$i?>> Choice Response
		<p>
<?
	}
?>
	<p>

<?	// makes a drop down list of all the section headings // ?>
	Section: 
	<select name=section>
<?
	$query = <<< ENDOFQUERY
	select * from section
ENDOFQUERY;
	$secs = $db->getSELECT($query);
	if (is_array($secs)) {
		$secs = $secs['results'];
		for ($i = 1; $i <= count($secs); $i++) {
			$secid = $secs[$i]['section_id'];
			$sectext = substr($secs[$i]['section_text'],0,60);
?>
			<option value=<?=$secid?>><?=$sectext?></option>
<?
		}
	}
?>
	</select><p>
	<input type=hidden name=sem value=<?=$sem?>>
	<input type=hidden name=year value=<?=$year?>>
	<input type=hidden name=sid value=<?=$surveyid?>>
	<input type=submit name=send value="Add Question"></form></td></tr></table>
	<p>

<? // form for adding a section to a survey // ?>
	<table border=1 width=500><tr><td>
	<font size=4><b>Add Section</b></font><p>
	<form name=addsec method=post action=?page=edsurv onSubmit="return validate_section ( );">
	<input type=hidden name=numquests value=<?=$numqs?>>
	<input type=hidden name=sem value=<?=$sem?>>
	<input type=hidden name=year value=<?=$year?>>
	<input type=hidden name=sid value=<?=$surveyid?>>
	<select name=section>
<?

	$query = <<< ENDOFQUERY
	select * from section
ENDOFQUERY;
	$secs = $db->getSELECT($query);
	if (is_array($secs)) {
		$secs = $secs['results'];
		for ($i = 1; $i <= count($secs); $i++) {
			$secid = $secs[$i]['section_id'];
			$sectext = substr($secs[$i]['section_text'],0,60);
?>
			<option value=<?=$secid?>><?=$sectext?></option>
<?
		}
	}
?>
	<option value=Other>Other</option></select><p>
	Other: <textarea name=newsec></textarea><br>
	Question: <input type=text size=4 name=secbeg> to <input type=text size=4 name=secend><br>
	<input type=submit name=send value="Add Section">
	</form></td></tr></table><P>
<?

	// gets questions that already are in current survey //
	$query = <<< ENDOFQUERY
	select question_order, question.question_id, question_text, section_id from question, survey_question where semester_year_id=$syid and
	survey_id=$surveyid and question.question_id = survey_question.question_id
	ORDER BY question_order
ENDOFQUERY;

	$sq = $db->getSELECT($query);

	echo "<font size=4><b>Existing Survey Questions</b></font><br>";

	// if there are existing questions
	if (is_array($sq)) {
		$sq = $sq['results'];
		
		$secid = "";
		for ($i = 1; $i <= count($sq); $i++) {
			
			echo "<form action=?page=edsurv method=post>"; 
			$qname = "qd" . $i;
			$qid = $sq[$i]['question_id'];
			$currsec = $sq[$i]['section_id'];
			if ($secid != $currsec) {
				$secid = $currsec;
				$query = <<< ENDOFQUERY
				select section_text from section where section_id=$secid
ENDOFQUERY;
				$sectext = $db->getSELECT($query);
				echo "<b>" . $sectext['results'][1]['section_text'] . "</b><p>";
			}
			
?>
			<p>
		<?	///// form for deleting question //// ?>
			<table width=500 border=1><tr><td>
			<input type=submit name=send value="Delete Question">			
			<input type=hidden name=qid value=<?=$qid?>>
			<input type=hidden name=sem value=<?=$sem?>>
			<input type=hidden name=year value=<?=$year?>>
			<input type=hidden name=sid value=<?=$surveyid?>>
			</form>
<?
			// displays question
			echo "<b>" . $sq[$i]['question_order'] . ". " . $sq[$i]['question_text'] . "</b><p>";

			// gets choices tied to learning outcomes that are no longer choices to question and removes them
			$query = <<< ENDOFQUERY
			select choice_id from choice_bar where question_id = $qid and choice_id not in (select choice_id from question_choice where question_id=$qid and semester_year_id=$syid)
ENDOFQUERY;
			$delchoices = $db->getSELECT($query);
			if (is_array($delchoices)) {
				$delchoices = $delchoices['results'];

				for ($j = 1; $j <= count($delchoices); $j++) {
					$c = $delchoices[$j]['choice_id'];
					$query = <<< ENDOFQUERY
					delete from choice_bar where question_id=$qid and choice_id=$c
ENDOFQUERY;
					mysql_query($query);
				}
			}

			// gets the choices to the question
			$query = <<< ENDOFQUERY
			select choice_text, choice.choice_id, choice_order, form_element from choice, question_choice where question_id=$qid and semester_year_id=$syid and
			choice.choice_id=question_choice.choice_id
			ORDER BY choice_order
ENDOFQUERY;
			$chs = $db->getSELECT($query);
			$form = "";

			// if there are choices
			if (is_array($chs)) {
				
				$choicearray = array();
				$chs = $chs['results'];
				$form = $chs[1]['form_element'];
				if ($form == 3) {
					echo "<b>Multiple Choice</b><p>";
				}
				else {
					echo "<b>Multiselect</b><p>";
				}

				// checks to see if question needs to be categorized or not
				$query = <<< ENDOFQUERY
				select * from noncat_question where question_id=$qid
ENDOFQUERY;
				$cat = $db->getSELECT($query);
				if (!is_array($cat)) {
					echo "<b>Categorize Question</b><p>";
				}
				else {
					echo "<b>Don't Categorize Question</b><p>";
				}

?>
				<table border=1 width=450><tr><td>
				<form action=?page=edsurv method=post>
				<input type=hidden name=sem value=<?=$sem?>>
				<input type=hidden name=year value=<?=$year?>>
				<input type=hidden name=sid value=<?=$surveyid?>>
				<input type=hidden name=qid value=<?=$qid?>>
<?
				// displays choice
				for ($j = 1; $j <= count($chs); $j++) {
					$choicearray[$j] = $chs[$j]['choice_id'];
					$chname = "dc" . $j;
					$ch = $chs[$j]['choice_text'];
					$chorder = $chs[$j]['choice_order'];
?>
					<input type=checkbox name=<?=$chname?> value=<?=$chorder?>>
<?
					echo  $chletter[$j] . ". " . $ch . "<br>";
				}
?>
				<? // button deleting selected choices // ?>
				<input type=submit name=send value="Delete Choice(s)"><br>
				</form></td></tr></table><p>
<?
				// gets learning outcomes tied to question
				$query = <<< ENDOFQUERY
				select outcome_id, choice_bar.choice_id, choice_order, low_val, high_val, bar.bar_id, bar_meaning, outcome_text from outcome, choice_bar, bar,question_choice where
				choice_bar.question_id=$qid and bar_meaning=outcome_id and choice_bar.bar_id=bar.bar_id and choice_bar.choice_id=question_choice.choice_id and
				question_choice.question_id=$qid and semester_year_id=$syid
				ORDER BY outcome_text, low_val, high_val, choice_order
ENDOFQUERY;
				$LOchoices = $db->getSELECT($query);

				// if there are learning outcomes
				if (is_array($LOchoices)) {
					echo "<table border=1 width=450><tr><td><b>Existing Outcomes</b><p>";
					$LOchoices = $LOchoices['results'];
					$pastOut = "";
					$pastLow = "";
					$pastHigh = "";

					// goes through each choice tied to a learning outcome
					for ($k = 1; $k <= count($LOchoices); $k++) {

						// if this is a different learning outcome
						if ( ($pastOut != $LOchoices[$k]['outcome_text']) || ($pastLow != $LOchoices[$k]['low_val']) || ($pastHigh != $LOchoices[$k]['high_val'])) {
							
							// if this isn't the first learning outcome choice, displays form for deleting a learning outcome
							if ($k != 1) {
?>
								<form action=?page=edsurv method=post>
								<input type=hidden name=sem value=<?=$sem?>>
								<input type=hidden name=year value=<?=$year?>>
								<input type=hidden name=sid value=<?=$surveyid?>>
								<input type=hidden name=qid value=<?=$qid?>>
								<input type=hidden name=barid value=<?=$barid?>>
								<input type=hidden name=out value=<?=$pastOutID?>>
								<input type=submit name=send value="Delete Outcome"><br>
								</form>
<?
							}
							// updates new learning outcome
							$pastOut = $LOchoices[$k]['outcome_text'];
							$pastLow = $LOchoices[$k]['low_val'];
							$pastHigh = $LOchoices[$k]['high_val'];
							$barid = $LOchoices[$k]['bar_id'];
							$pastOutID = $LOchoices[$k]['outcome_id'];

?>
							<p>
<?
							// displays learning outcome and choices tied to the learning outcome
							echo "<b>Outcome: </b>" . $pastOut . "<br>";
							echo "<b>Bar Range: </b>" . $pastLow . "-" . $pastHigh . "<br>";
							echo "<b>Choices: </b>";
						}
						for ($m = 1; $m <= count($choicearray); $m++) {
							if ($choicearray[$m] == $LOchoices[$k]['choice_id']) {
								echo $chletter[$LOchoices[$k]['choice_order']] . ",";
							}
						}
					}
?>
					<? //// displays form for deleting last learning outcome //// ?>
					<form action=?page=edsurv method=post>
					<input type=hidden name=sem value=<?=$sem?>>
					<input type=hidden name=year value=<?=$year?>>
					<input type=hidden name=sid value=<?=$surveyid?>>
					<input type=hidden name=qid value=<?=$qid?>>
					<input type=hidden name=barid value=<?=$barid?>>
					<input type=hidden name=out value=<?=$pastOutID?>>
					<input type=submit name=send value="Delete Outcome"><br>
					</form></td></tr></table>
					<p>
<?
				}

				// gets all learning outcomes into a drop down list
				$query = <<< ENDOFQUERY
				select * from outcome
ENDOFQUERY;

				$outcomes = $db->getSELECT($query);


				if (is_array($outcomes)) {
					$outcomes = $outcomes['results'];
					// form for adding a learning outcome, can enter in choices tied to outcome by choice letter, can also enter bar range
					echo "<p><table border=1 width=450><tr><td><b>Add Learning Outcome</b><p><form action=?page=edsurv method=post><select name=addoutcome>";

					for ($j = 1; $j <= count($outcomes); $j++) {
						$outid = $outcomes[$j]['outcome_id'];
						$outtext = $outcomes[$j]['outcome_text'];
?>
						<option value=<?=$outid?>><?=$outtext?></option>
<?
					}
?>
					</select><br>
					<input type=hidden name=sem value=<?=$sem?>>
					<input type=hidden name=year value=<?=$year?>>
					<input type=hidden name=sid value=<?=$surveyid?>>
					<input type=hidden name=qid value=<?=$qid?>>
					Range: <input type=text size=3 name=low> to <input type=text size=3 name=high><br>
					Letter Choices(sep by commas): <input type=text name=outchoices size=10><br>
					<input type=submit name=send value="Add Outcome"><br></form></td></tr></table>
<?
				}
				else {
					echo "No outcomes exist. Please add new learning outcome at bottom of page.<p>";
				}
?>
				<p>
				<table border=1 width=450><tr><td><b>New Learning Outcome</b><p>
				<form name=newoutcome action=?page=edsurv method=post onSubmit="return validate_newoutcome();">
				<input type=text name=newoc size=20><p>
				<input type=hidden name=sem value=<?=$sem?>>
				<input type=hidden name=year value=<?=$year?>>
				<input type=hidden name=sid value=<?=$surveyid?>>
				<input type=submit name=send value="Add New Outcome">
				</form></td></tr></table><p>
<?
			}
			// question is open response
			else {
				echo "<b>Open Response</b><p>";

				// checks to see if question needs to be categorized or not
				$query = <<< ENDOFQUERY
				select * from noncat_question where question_id=$qid
ENDOFQUERY;
				$cat = $db->getSELECT($query);
				if (!is_array($cat)) {
					echo "<b>Categorize Question</b><p>";
				}
				else {
					echo "<b>Don't Categorize Question</b><p>";
				}
			}

?>
			<? //// form for categorizing or decategorizing question //// ?>
			<form method=post action=?page=edsurv>
			<input type=radio name="cat" value=0 checked>Don't Catgorize</radio>
			<input type=radio name="cat" value=1>Categorize</radio><p>
			<input type=hidden name=qid value=<?=$qid?>>
			<input type=hidden name=sem value=<?=$sem?>>
			<input type=hidden name=year value=<?=$year?>>
			<input type=hidden name=sid value=<?=$surveyid?>>
			<input type=submit name=send value="Update Categorization">
			</form>
			<p>
		<?	///// form for adding a choice ///// ?>
			<table border=1 width=450><tr><td><b>Add Choice</b>
			<form action=?page=edsurv method=post>
			<input type=hidden name=sem value=<?=$sem?>>
			<input type=hidden name=year value=<?=$year?>>
			<input type=hidden name=sid value=<?=$surveyid?>>
			<input type=hidden name=qid value=<?=$qid?>>
			Choice Order: <input type=text name=choiceorder size=2><br> 
			Choice: <input type=text size=30 name=choicename><br>

<?
			// if form is blank, means that there are no choices for the question
			// so the admin can set the type of choices for the question if adding any choices
			if ($form == "") {
?>
				<input type=radio name=el value=3 checked>Multiple Choice</radio>
				<input type=radio name=el value=4>MultiSelect</radio><br>	
<?
			}
?>
			<? //// whether choice is open response or not //// ?>
			<input type=checkbox name=cr value=1> Choice Response<p>
			<input type=submit name=send value="Add Choice for Q<?=$i?>"><br>
			</form></td></tr></table><p></td></tr></table>
			<p><br>
<?
		}
		// displays form for deleting last question in survey
		if (count($sq) > 0) {
?>
			<form action=?page=edsurv method=post>
			<input type=hidden name=sem value=<?=$sem?>>
			<input type=hidden name=year value=<?=$year?>>
			<input type=hidden name=sid value=<?=$surveyid?>>
			<input type=submit name=send value="Delete All">
			</form>
<?
		}
	}
	else {
		echo "No questions.<br>"; 
	}
}
?>
<br>
<br>


<? ///// Form for changing the survey ///
//// can change the survey type, year, and semester //// ?>
<table border=1 width=500><tr><td><font size=4><b>Change Survey</b></font><p>
<form name=changesurvey action=?page=edsurv method=post onSubmit="return validate_survey();">
Surveys: 
<select name="sid">
<?
$query = <<< ENDOFQUERY
SELECT distinct survey_id, survey_name FROM survey
ENDOFQUERY;

$surveylist = $db->getSELECT($query);
if (!is_array($surveylist)) {
	echo "There are no surveys in the database.";
	die;
}
$surveylist = $surveylist['results'];
for ($i = 1; $i <= count($surveylist); $i++) {
	$si = $surveylist[$i]['survey_id'];
	$sn = $surveylist[$i]['survey_name'];	
?>
	<option value=<?=$si?>><?=$sn?></option>;
<?
}
?>
</select><p>
Year: <input type=text name=year size=4 value=200><p>
Semester: <select name=sem>
<option value=1>Spring</option>
<option value=2>Summer</option>
<option value=3>Fall</option>
</select><p>

<input type=submit value="Change Survey">
</form></td></tr></table>
<p>

<? ///// form for adding a new survey type ///// ?>
<table border=1 width=500><tr><td><font size=4><b>Add Survey Type</b></font><p>
<form name=surveytype action=?page=edsurv method=post onSubmit="return validate_surveytype();">
Survey Name: <input type=text name=surveyname size=40> <p>
Survey Text:<br>
<textarea name=surveytext></textarea><p>
<input type=submit name=send value="Add Survey Type">
</form></td></tr></table>
