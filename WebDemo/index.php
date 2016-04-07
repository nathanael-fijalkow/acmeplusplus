<?php
session_start();
$iid = session_id();
		
$dir = $iid."@".$_SERVER['REMOTE_ADDR'];

$finput="automaton.".$dir.".acme";
$foutput="computation.".$dir.".log";
$input="demo/".$finput;
$output="demo/".$foutput;
$bin="webdemo";
$input=$finput;
$output=$foutput;
$bin = realpath("acmeplusplus/build/WebDemo");


if(isset($_POST['action']))
  {
    $action = $_POST['action'];
    switch($action)
      {
      case 'compute-monoid':
      case 'has-value1':
      case 'is-bounded':
	$aut=$_POST['automaton'];

	//starts a new computation
	//we create the computation dir
	//		     if(!is_dir($dir))
	//		         mkdir($dir,"0777",true);
	//we create the input file


	$f = fopen($input,"w+");
	$o = fopen($output,"w+");
	if($f === FALSE)
	  {
	    $msg = "Failed to create input automaton file";
	    fwrite($o,$msg);
	    echo $msg;
	    break;
	  }
	else if($aut["initial"] == '')
	  {
	    $msg = "This automaton has no initial state.";
	    fwrite($o,$msg);
	    break;
	  }
	else if($aut["final"] == '')
	  {
	    $msg = "This automaton has no final state.";
	    fwrite($o,$msg);
	    break;
	  }
	else
	  {

	    //we send data to the file
	    $type = $aut["type"];
	    $snb = $aut["statesnb"];
	    $lnb = $aut["lettersnb"];
	    fwrite($f,$snb."\n".$type."\n");
	    $c='a';
	    for($i=0; $i < $lnb; $i++)
	      fwrite($f,$c++);
	    fwrite($f,"\n".$aut["initial"]);
	    fwrite($f,"\n".$aut["final"]);
	    fwrite($f,"\n");
	    $c='a';
	    for($i=0; $i < $lnb; $i++)
	      {
		fwrite($f,$c++."\n");
		fwrite($f,$aut["mats"][$i]);
	      }
	    fclose($f);
	    fwrite($o,'The <a href="'.$input.'"> automaton file</a> and '); 
	    fwrite($o,'the <a href="'.$output.'"> log file</a>'."\n"); 
	    fclose($o);
	    echo "Computation started";	
	    exec("nice ".$bin." ".$action." ".$finput." >> ".$foutput. " 2>/dev/null  &");
	  }
	break;
      case 'progress':
	$size = filesize($output);
	$max = 10000;

	//retourne le contenu du fichier de sortie
	$f =fopen($output,"r");
	if($f ===false)
	  {
	    echo "No output yet";
	    break;
	  } 
	$out = "";

	if($size <= $max)
	  {
	while(true)
	  {
	    $chunk  = fgets($f);
	    if($chunk === false)
	      break;
	    echo str_replace(array("\n","E ","O "),array("<br/>","&epsilon;","&omega;"),$chunk);		
	  }
	  }
	else
	  {
	    $chunk  = fgets($f);
	    echo str_replace(array("\n","E ","O "),array("<br/>","&epsilon;","&omega;"),$chunk);		
	    $chunk  = fgets($f);
	    echo str_replace(array("\n","E ","O "),array("<br/>","&epsilon;","&omega;"),$chunk);		

	    echo "<br/>*************************************************<br/>";
	    echo "<br/>The computation is long, output is truncated...";
	    echo "Here is <a href=\"".$foutput."\">the complete log.</a><br/>";
	    echo "<br/>*************************************************<br/>";

	    fseek($f, $size - $max);
	    $chunk  = fgets($f);

	    while(true)
	      {
	    $chunk  = fgets($f);
	    if($chunk === false)
	      break;
	    echo str_replace(array("\n","E ","O "),array("<br/>","&epsilon;","&omega;"),$chunk);		
	      }
	  }
	fclose($f);
	break;
      case 'stop':
	//tue le processus en cours
	break;
      }
  }
else
  {
    ?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Acme++ Online</title>
<style>
   table, th, td {
 margin:5px;
 }
   body {
   margin: 10% 10% 10% 10%;
   }
</style>
</head>
<body margin="10">
      <script src="jquery-2.2.0.min.js" type="text/javascript"></script>
<center><h1>Acme++ Online</h1></center>

      <p>Welcome on the demo page of Acme++,
      a tool manipulating stabilization monoids in order to solve three algorithmic problems.
      </p>
      <ul>
      <li> Computing the star-height of a regular language,
      i.e. the minimal number of nested Kleene stars needed
      for expressing the language with a regular expression.</li>
		       <li> Deciding boundedness and equivalence
		       for regular cost functions.</li>
				     <li>Deciding whether a leaktight probabilistic automaton has value 1,
				     i.e. whether the automaton accepts some words
				     with probability arbitrarily close to 1.</li>
				     </ul>
				     <p>
				     These three problems reduce to the computation
				     of the stabilization monoid associated with the automaton,
				     which is a challenge since the monoid is exponentially larger than the automaton.
				     The compact data structures used in Acme++, together with optimizations and heuristics,
				     allow this program to handle automata with several hundreds of states.</p>
				     <p>
				     The project source code is available <a href="https://github.com/nathanael-fijalkow/acmeplusplus">on Github</a>.<br/>
Bug reports and requests should be sent to [hugo dot gimbert at cnrs dot fr].</p>
<hr/>
				     <form method="post">
				     <table>
				     <tr>
				     <td>
				     <b>Type of automaton</b>

				     <select class="matchanger" id="auttype">
				     <option selected="selected" value="counter">with Counters</option>
				     <option value="proba">Probabilistic</option>
				     </select>
</td></tr><tr><td>
				     <b>Letters</b>
				     <select class="matchanger" name="letters" id="lettersnb"></select>
				     <b>states</b>
				     <select class="matchanger" name="states" id="statesnb"></select>
				     <span id="countersnbdiv">
				     <b>counters</b>
				     <select class="matchanger" id="countersnb"></select>
				     </span>
				     </td></tr>
				     <tr><td>
				     <table><tr>
				     <td style="vertical-align:top">
				     <b>Initial</b>
				     <span class="initial"></span>
				     </td><td>&nbsp;</td>
				     <td style="vertical-align:top">
				     <b>Final</b>
				     <span class="final"></span>
				     </td>
				     </td><td>&nbsp;</td>
				     <td>
				     <span id="probamats" ></span>
				     <span id="countermats" ></span>
				     </td>
				     </tr>
				     </table></td></tr>
				     <tr>
				     <td class="density" >Density
				     <select id="density" />
				     <?php
				     for($i=10;$i <=40; $i+=10)
				       echo '<option value="'.$i.'">'.$i.'%</option>';
    echo '<option selected="selected" value="50">50%</option>';
    for($i=60;$i <=90; $i+=10)
      echo '<option value="'.$i.'">'.$i.'%</option>';
    ?>
      </select>
	  </td>
	  </tr>

	  </table>
	  <div id="buttons">
	  <input type="button" id="random" value="Random" ></input>
	  <input type="button" class="compute" id="compute-monoid" value="Compute the monoid" ></input>
	  <span  id="probabdiv">
	  <input type="button" class="compute" id="has-value1" value="Check value 1" ></input>
	  </span>
	  <span id="counterbdiv">
	  <input type="button" class="compute" id="is-bounded" value="Check boundedness" ></input>
	  </span>


	  </div>
	  </form>
	  <div class="log">
	  </div>
<hr/>
	  <h3>Output</h3>
	  <div class="output">
	  </div>
	  <script>
	  var maxstatesnb = 10;
    var maxlettersnb = 4;
    var maxcountersnb = 5;
    var initialstatesnb = 3;
    var initiallettersnb =2;
    var initialcountersnb =2;

    function auttype()
    {
      return $('#auttype').val();
    }
    function lettersnb()
    {
      return $('#lettersnb').val();
    }
    function statesnb()
    {
      return $('#statesnb').val();
    }
    function countersnb()
    {
      return $('#countersnb').val();
    }

    generate();
    update();
    fill_random();

    setInterval(showProgress, 1000);

    function generate()
    {
      /* Generate statesnb selector */
      $('#statesnb').empty();
      for(i=1; i <= maxstatesnb; i++)
	{
	  var sell = (i == initialstatesnb) ? 'selected="selected"' : '';
	  $('#statesnb').append('<option '+sell+'>'+i+'</option>');
	}

      /* Generate lettersnb selector */
      $('#lettersnb').empty();
      for(i=1; i <= maxlettersnb; i++)
	{
	  var sell = (i == initialstatesnb) ? 'selected="selected"' : '';
	  $('#lettersnb').append('<option '+sell+'>'+i+'</option>');
	}

      /* Generate countersnb selector */
      var cselect = $('#countersnb');
      cselect.empty();
      for(var i = 1 ; i < maxcountersnb; i++)
	{
	  if(i == initialcountersnb)
	    cselect.append('<option selected="selected">'+i+'</option>');
	  else
	    cselect.append('<option>'+i+'</option>');
	} 

      /* Generate initial and fnal  selector */
      $('.initial').empty();
      $('.final').empty();
      for(i=1; i <= maxstatesnb; i++)
	{
	  $('.initial').append('<div  id="i'+i+'">'+i+'<input class="c" type="checkbox"></input></div>');
	  $('.final').append('<div  id="f'+i+'">'+i+'<input class="c" type="checkbox"></input></div>');
	}

      /* Generate matrix editor */
      $('#probamats').empty();
      $('#countermats').empty();
      for(i=1; i <= maxlettersnb; i++)
	{
	  var smat = '<div style="float:left;margin-right:30px" id="Mat'+i+'"><b>Mat '
	    +String.fromCharCode(97 + i - 1)+'</b></div>';
	  $('#probamats').append(smat);
	  $('#countermats').append(smat);
	  var pmat = $('#probamats').find('#Mat'+i);
	  var cmat = $('#countermats').find('#Mat'+i);
	  pmat.append('<br/>');
	  cmat.append('<br/>');
	  for(j=1; j<= maxstatesnb; j++)
	    {
	      var lab = 'l'+i+'_'+j;
	      pmat.append('<div id="'+lab+'">'+j+'</div>');
	      cmat.append('<div id="'+lab+'">'+j+'</div>');
	      var pline =pmat.find('#'+lab);
	      var cline =cmat.find('#'+lab);
	      for(k=1; k<= maxstatesnb; k++)
		{
		  pline.append('<input selected="" class="c" type="checkbox" id="c'+i+'_'+j+'_'+k+'"/>');
		  cline.append('<select selected=""  id="c'+i+'_'+j+'_'+k+'"/>');
		}
	    }
	}
    }


    function fill_random()
    {
      $('.c').prop('checked',false);
      var ok = false;
      var density = $('#density').val() / 100.0;
      var ccoeff = counters_list();

      /* random choice of final states */
      for(j=1; j<= statesnb; j++)		 
	{
	  var yes = Math.random() < density;
	  $('#f'+j).find("input").prop('checked',yes);
	  ok |= yes;
	}

      /* random choice of one initial state */
      var randinit = 1 + Math.floor( Math.random() * statesnb() );
      $('#i'+randinit).find("input").prop('checked',true);
      //at least one final state or parsing wont work
      if(!ok)
	{
	  randinit = 1 + Math.floor( Math.random() * statesnb() );
	  $('#f'+randinit).find("input").prop('checked',true);
	}

      var res = '';
      var pmat = $('#probamats');
      var cmat = $('#countermats');
      for(i=1; i <= maxlettersnb; i++)
	{
	  for(j=1; j<= maxstatesnb; j++)
	    {
	      for(k=1; k<= maxstatesnb; k++)
		{
		  var pcell = pmat.find('#c'+i+'_'+j+'_'+k);
		  pcell.prop('checked', (Math.random() < density));
		  var ccell = cmat.find('#c'+i+'_'+j+'_'+k);
		  ccell.val(ccoeff[Math.floor(Math.random()*ccoeff.length)]);
		}
	      var cell = pmat.find('#c'+i+'_'+j+'_'+(1 + Math.floor(Math.random()*statesnb)));
	      cell.prop('checked',true);
	    }
	}
    }


    function counters_list()
    {
      var result = [ ["B"," "], ["O","&omega;"] , ["E","&epsilon;"] ];
      var coef = '';
      for(var i = 1; i <= countersnb(); i++)
	{	
	  result.push(['I'+i,'i'+i]);
	  result.push(['R'+i, 'r'+i]);
	}
      return result;
    }


    function update()
    {
      for(i=1; i <= maxstatesnb; i++)
	{
	  if(i <= statesnb())
	    {
	      $('#i'+i).show();
	      $('#f'+i).show();
	    }
	  else
	    {
	      $('#i'+i).hide();
	      $('#f'+i).hide();
	    }		
	}

      if(auttype() == "proba")
	{	    
	  $('#countersnbdiv').hide();
	  $('#countermats').hide();
	  $('#probamats').show();
	  $('#probabdiv').show();
	  $('#counterbdiv').hide();
	  $('.density').show();
	}
      else
	{
	  $('#countersnbdiv').show();
	  $('#probamats').hide();
	  $('#countermats').show();
	  $('#probabdiv').hide();
	  $('#counterbdiv').show();
	  $('.density').hide();
	}
      var cselect = '';
      var counters = counters_list();
      for(var i = 0 ; i < counters.length; i++)
	cselect += '<option value='+counters[i][0]+'>'+counters[i][1]+'</option>';
      
      for(i=1; i <= maxlettersnb; i++)
	{
	  var pmat = $('#probamats').find('#Mat'+i);
	  var cmat = $('#countermats').find('#Mat'+i);

	      if( i > lettersnb()) { pmat.hide(); cmat.hide(); }
	      else if(auttype() == "proba") {   pmat.show(); cmat.hide(); }
	      else {  pmat.hide(); cmat.show(); }

	      for(j=1; j<= maxstatesnb; j++)
		{
		  var pline = pmat.find('#l'+i+'_'+j);
		  var cline = cmat.find('#l'+i+'_'+j);
		  if(j > statesnb())  {  pline.hide(); cline.hide(); }
		  else { pline.show();  cline.show(); }

		  for(k=1; k<= maxstatesnb; k++)
		    {
		      var lab= '#c'+i+'_'+j+'_'+k;
		      var ccell = cmat.find(lab);
		      var pcell = pmat.find(lab);
		      //		      if(auttype() == "counter")
			{
			  /* Updating counters selector */
			  var old = ccell.val();
			  ccell.empty();
			  ccell.append(cselect);
			  ccell.val(old);
			  if(ccell.val() == "")
			    ccell.val(cselect[0][0]);
			}
		      if(k <= statesnb()) { ccell.show(); pcell.show(); }
		      else { pcell.hide(); ccell.hide(); }
		    }
		}
       	}	
    }

    function output(data)
    {
      $('.output').empty();
      $('.output').append(data);
    }
    function log(data)
    {
      $('.log').empty();
      $('.log').append(data);
    }

    function showProgress()
    {
      $.ajax({
	method: "POST",
	    url: "",
	    async:false,
	    data: { "action": "progress"}
	})
	.done(function( msg ) {
	    output(msg );
	  });
    }



    $( ".matchanger" ).change(function() {
	update();
      });

    $("#random").click(function() {
	fill_random();
      });

    $(".compute").click(function() {
	var mats = [];
	var initial = '';
	var final = '';
	for(i=1; i <= statesnb(); i++)
	  {
	    var obj = $('#f'+i);
	    if($('#f'+i).find("input").prop('checked')) final += (i-1) + " ";
	    if($('#i'+i).find("input").prop('checked')) initial += (i-1) + " ";
	  }
	for(i=1; i <= lettersnb(); i++)
	  {	 
	    var st = '';
	    for(j=1; j <= statesnb(); j++)
	      {
		for(k=1; k <= statesnb(); k++)
		  {
			
		    if(auttype() == "proba")
		      {
			var $cell = $('#probamats').find('#c'+i+'_'+j+'_'+k);
			st += $cell.prop('checked') ? "1 " : "_ ";
		      }
		    else
		      {
			var $cell = $('#countermats').find('#c'+i+'_'+j+'_'+k);
			st += $cell.val()+ " ";
		      }
		  }
		st+="\n";
	      }
	    mats.push(st);
	  }
	var aut = {
	  "type" : (auttype() == "proba") ? "p" : countersnb(),
	  "statesnb" : statesnb(),
	  "lettersnb" : lettersnb(),
	  "initial" : initial,
	  "final" : final,
	  "mats" : mats
	};
	
	$.ajax({
	  method: "POST",
	      url: "",
	      data: { "action": this.id, "automaton" : aut }
	  })
	  .done(function( msg ) {
	      output(msg );
	    });
      });

    </script>
	<br/>
	<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>

	</body>
	<?php
	}

?>
</body>
</html>