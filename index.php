<? require_once('bootstrap.php'); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
	<title><?= NAME ?></title>
	<link rel="stylesheet" href="<?= BASE_URL ?>assets/bootstrap/css/bootstrap.css" type="text/css" media="screen" >

	<style type="text/css" media="screen">
		body{
			padding-top: 60px;
		}
        .category > UL {
            display: none;
        }
	</style>
</head>
<body>

<div class="navbar navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container">
      <a class="brand" href="index.php"><?= NAME ?></a>
      <ul class="nav">
		<?
		foreach( $navigation as $name => $url )
			echo "<li><a href=$url>$name</a></li>";
		?>
      </ul>
    </div>
  </div>
</div>
<div class="container">
    <div class="row">
	    <? render_page(); ?>
    </div>
</div>
<script src="<?= BASE_URL ?>assets/js/jquery.js"></script>
<script src="<?= BASE_URL ?>assets/js/bootstrap.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/tabby.js"></script>
<script src="<?= BASE_URL ?>assets/js/application.js"></script>

<script type="text/javascript">
    $(document).ready(function(){

        $("ul.subnav").parent().append("<span></span>"); //Only shows drop down trigger when js is enabled (Adds empty span tag after ul.subnav*)

        $("ul.topnav li span").click(function() { //When trigger is clicked...

            //Following events are applied to the subnav itself (moving subnav up and down)
            $(this).parent().find("ul.subnav").slideDown('fast').show(); //Drop down the subnav on click

            $(this).parent().hover(function() {
            }, function(){
                $(this).parent().find("ul.subnav").slideUp('slow'); //When the mouse hovers out of the subnav, move it back up
            });

            //Following events are applied to the trigger (Hover events for the trigger)
        }).hover(function() {
                $(this).addClass("subhover"); //On hover over, add class "subhover"
            }, function(){	//On Hover Out
                $(this).removeClass("subhover"); //On hover out, remove class "subhover"
            });

    });

</script>
</body>
</html>