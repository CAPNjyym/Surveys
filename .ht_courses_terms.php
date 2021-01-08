<?
// This page allows you to manage courses and terms
	// You can view existing courses and terms
	// You can add new courses and terms
	// You CANNOT remove current courses or terms
		// This is intentionally left out so that you cannot remove
		//    a course or term when it is used by a class or other data

// Semesters
$sems[1] = "Spring ";
$sems[2] = "Summer ";
$sems[3] = "Fall ";
?>
<html>
<head>
<Script language = JavaScript>
function validate_course()
{
	prg = document.newcourse.program.value;
	num = document.newcourse.number.value;
	var ValidPrgs = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
   	var ValidNums = "0123456789";
   	var Char;
	
	if (prg.length != 4)
	{
		alert("Program must be four letters.");
		return false;
	}
	if (num.length != 3)
	{
		alert("Course number must be three numbers.");
		return false;
	}
	
  	for (i=0; i<4; i++)
	{ 
      	Char = prg.charAt(i);
		
      	if (ValidPrgs.indexOf(Char) == -1) 
        {
			alert("Program must consist only of capital letters.");
			return false;
        }
	}
  	for (i=0; i<3; i++)
	{ 
      	Char = num.charAt(i);
		
      	if (ValidNums.indexOf(Char) == -1) 
        {
			alert("Course number must consist only of numbers.");
			return false;
        }
	}
	
    return true;
}

function validate_term()
{
	year = document.newterm.year.value;
   	var ValidNums = "0123456789";
   	var Char;
	
  	for (i=0; i<year.length; i++)
	{ 
      	Char = year.charAt(i);
		
      	if (ValidNums.indexOf(Char) == -1) 
        {
			alert("Year must consist only of numbers.");
			return false;
        }
	}
	
    return true;
}

</Script>
</head>
<h1 align=center>Manage Courses and Terms</h1>
<?

// Insert new Course (if it doesn't already exist)
if ($_POST['send'] == "Create Course")
{
	$program = $_POST['program'];
	$number = $_POST['number'];
	
	$query = <<< ENDOFQUERY
	SELECT course_id from course where program='$program' and course=$number
ENDOFQUERY;
	$exists = $db->getSELECT($query);
	
	echo($program.' '.$number);
	if (is_array($exists))
		echo(' already exists.');
	else
	{
		// Gets this course's id
		$query = <<< ENDOFQUERY
		SELECT max(course_id) as id from course
ENDOFQUERY;
		$id = $db->getSELECT($query);
		$id = $id['results'][1]['id'] + 1;
		
		$db->insertRow(array('table'=>'course', 'course_id'=>$id, 'program'=>$program, 'course'=>$number, 'required'=>0));
		
		echo(' inserted.');
	}
}

// Insert new Term (if it doesn't already exist)
else if ($_POST['send'] == "Create Term")
{
	$semester = $_POST['semester'];
	$year = $_POST['year'];
	
	$query = <<< ENDOFQUERY
	SELECT semester_year_id from semester_year where semester=$semester and year=$year
ENDOFQUERY;
	$exists = $db->getSELECT($query);
	
	echo($sems[$semester].$year);
	if (is_array($exists))
		echo(' already exists.');
	else
	{
		// Gets this course's id
		$query = <<< ENDOFQUERY
		SELECT max(semester_year_id) as id from semester_year
ENDOFQUERY;
		$id = $db->getSELECT($query);
		$id = $id['results'][1]['id'] + 1;
		
		$db->insertRow(array('table'=>'semester_year', 'semester_year_id'=>$id, 'semester'=>$semester, 'year'=>$year));
		
		echo(' inserted.');
	}
}


//////////////////////////////////////////////////////////
////////////////////// Manage Courses ////////////////////
//////////////////////////////////////////////////////////

$query = <<< ENDOFQUERY
	SELECT * from course
	order by program, course
ENDOFQUERY;
$courses = $db->getSELECT($query);

?>
<table border=1 width=500><tr><td>
<font size=4><b>Manage Courses</b></font>
<?
if (is_array($courses))
{
	$courses = $courses['results'];
?>
	<form><p>Current Courses: <select name=course>
<?
	for ($i=1; $i<=count($courses); $i++)
	{
		$c = $courses[$i];
		echo "<option value=" . $c['course_id'] . ">" . $c['program'] . " " . $c['course'] . "</option>";
	}
?>
	</select></form>
<?
}
?>
<! Create new Course >
<br><h4>Add New Course</h4>
<form name=newcourse method=post action=?page=managecat onSubmit="return validate_course();">
	Program: <input name=program type=text size=8>
	Number: <input name=number type=text size=8>
	<input type="submit" name="send" value="Create Course">
</form>
</td></tr></table><br>
<?

//////////////////////////////////////////////////////////
/////////////////////// Manage Terms /////////////////////
//////////////////////////////////////////////////////////

$query = <<< ENDOFQUERY
	SELECT * from semester_year where semester!=0
	order by year, semester
ENDOFQUERY;
$terms = $db->getSELECT($query);

?>
<table border=1 width=500><tr><td>
<font size=4><b>Manage Terms</b></font>
<?
if (is_array($terms))
{
	$terms = $terms['results'];
?>
	<form><p>Current Terms: <select name=course>
<?
	for ($i=1; $i<=count($terms); $i++)
	{
		$t = $terms[$i];
		echo "<option value=" . $t['semester_year_id'] . ">" . $sems[$t['semester']] . $t['year'] . "</option>";
	}
?>
	</select></form>
<?
}
?>
<! Create new Term >
<br><h4>Add New Term</h4>
<form name=newterm method=post action=?page=managecat onSubmit="return validate_term();">
	Semester: <select name=semester>
		<option value=1>Spring</option>
		<option value=2>Summer</option>
		<option value=3>Fall</option></select>
	Year: <input name=year type=text size=8>
	<input type="submit" name="send" value="Create Term">
</form>
</td></tr></table>
