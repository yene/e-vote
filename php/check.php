
<?php
include "getJSON.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Stimme prüfen</title>
<meta name="description" content="...">
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="bootstrap-reboot.min.css">
<style>
#main {
  margin: 0 auto;
  margin-top: 100px;
  width: 600px;
}
</style>
</head>
<body>
<div id="main">
  <h2>Resultat</h2>
    <div>
      Anonymisierter Name: <span id="name"></span>
    </div>
    <br>
    <div>
      <ul id="list">
      </ul>
    </div>
    <div>
      Kryptografischer Fingerabdruck: <span id="shorthash"></span>
    </div>
  </form>
</div>

<script>
var dataJSON = '<?=getJSON()?>';
var data = JSON.parse(dataJSON);

var ul = document.getElementById('list');
data.result.forEach((r) => {
  var newLI = document.createElement('li');
  newLI.appendChild(document.createTextNode(r.Beschreibung + ' - ' + r.Stimme));
  ul.appendChild(newLI);
});


document.getElementById('name').innerHTML = data.name;
document.getElementById('shorthash').innerHTML = data.shorthash;


</script>

</body>
</html>
