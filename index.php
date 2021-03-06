<html>
<?php
$debug = 1;
if ($debug == 0) {
	$verbose = '-q --no-warnings';
} else {
	$verbose = '';
}
$cmd = 'youtube-dl --no-playlist --restrict-filenames --prefer-ffmpeg';
ini_set('max_execution_time',300);
if (!(isset($_POST['video']) && isset($_POST['options']) && isset($_POST['nameStyle']))) {
	$video = $options = $nameStyle = "";
} else {
	$video = sanitize_input($_POST['video']);
	$options = sanitize_input($_POST['options']);
	$nameStyle = sanitize_input($_POST['nameStyle']);
}
function sanitize_input($data) {
	$data = trim($data);
	$data = escapeshellcmd($data);
	return $data;
}
function correct_ext(&$filename, $valid) {
	$filename = shell_exec("ls -1 | grep -E \"$filename\".$valid");
	$filename = trim(preg_replace('/\s+/', ' ', $filename));
}
function push_file($file) {
	if (file_exists($file)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Content-Transfer-Encoding: binary\n');
		header('Connection: Keep-Alive');
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Length: '.filesize($file));
		set_time_limit(0);
		ob_clean();
		flush();
		readfile($file);
		unlink($file);
	}
}
if ($video && $options && $nameStyle) {
	$file = shell_exec("$cmd --get-$nameStyle $video");
	if ($options == "Video") {
		shell_exec("$cmd --embed-subs -f 'bestvideo[ext=mp4]+bestaudio' --audio-quality 0 -o \"%($nameStyle)s.%(ext)s\" --xattrs $video $verbose");
		correct_ext($file, "'.mkv'\|'.webm'\|'.mp4'");
	} else if ($options == "Music") {
		shell_exec("$cmd --extract-audio --embed-thumbnail --audio-format mp3 --audio-quality 0 -f 'bestaudio' -o \"%($nameStyle)s.%(ext)s\" $video $verbose");
		correct_ext($file, "'.mp3'");
	} else if ($options == "Subtitles") {
		shell_exec("$cmd --skip-download --write-auto-sub -o \"%($nameStyle)s.%(ext)s\" $video $verbose");
		correct_ext($file, "'.vtt'");
		shell_exec("sed -i -e 's/<[^>]*>//g' $file");
	}
	push_file($file);
}
?>
<head>
	<title>YouTube Downloader</title>
</head>
<style>
html, body{
  margin:0;
  padding:0;
  height:100%
}
body{
  background-image:url('https://img1.picload.org/image/rwgoolrr/bg.jpg');
  background-size: cover;
  position: relative;
  margin-right: 10%;
  margin-left: 10%;
  top: 50%;
  transform: translate(0, -50%);
  color: #EEEADF;
  text-align: center;
  font-size: 32px;
  font-family: monospace;
  text-shadow: 2px 2px 2px rgba(0, 0, 0, 1);
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  cursor: default;
}
input, select{
  -webkit-appearance: none;
  -moz-appearance: none;
  text-indent: 0px;
  text-overflow: '';
  font-size: 32px;
  text-align: left;
  margin-top: 5%;
  position: relative;
  color: #000000;
  width: 66%;
  font-family: monospace;
  border: 0px;
  background: rgba(0, 0, 0, 0);
}
</style>
<body>
<div>
<form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
	<input type="text" name="video" placeholder="VideoID" autofocus>
		<br><br>
	<select name="options" onChange"combo(this, 'options')">
		<option>Video</option>
		<option>Music</option>
		<option>Subtitles</option>
	</select>
		<br><br>
	<select name="nameStyle" onChange"combo(this, 'sameStyle')">
		<option value="title">Title</option>
		<option value="id">ID</option>
	</select>
		<br><br>
	<input type="submit" name="submit" style="visibility:hidden;"/>
</form>
</div>
</body>
</html>
