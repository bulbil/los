<?php
session_start();
if(isset($_POST)) print_r($_POST);
include '../html/header.html';
include '../includes/utilities.php';
include '../html/masthead.html';
?>

<div class='row'>
<div class='col-md-8 col-md-offset-2'>
  <form class='form-horizontal' action='test.php' method='post'>

  <div class='form-group col-md-8'>
  
  <h4>text</h4>
  <input id='first' class='form-control' type='text' name='chunk' value='what' />
  
  <h4>tags</h4>
  <input type='text' id='what' name='select'>
  
  <h4>multiple</h4>
  <input type='text' id='whatwhat' name='some'>

  <h4>multiples 2</h4>
  <select multiple id='multiples' style='width:100%'>
    <option>one</option>
    <option>two</option>
    <option>three</option>
    <option>four</option>
    <option>five</option>    
  </select>


  <h4>multiple choice list</h4>
  <select multiple id='choices' style='width:100%'>
    <option>one</option>
    <option>two</option>
    <option>three</option>
    <option>four</option>
    <option>five</option>    
  </select>

  <input type='hidden' id='hidden' />
  <div id='add' class='btn btn-success'>add</div>
  <input class='btn' type='submit' value='submit'/>
  </form>
</div>
</div>
</div>




    </div><!-- /.container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="../js/jquery-1.10.2.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/select2.js"></script>

    <script>

      $('#what').select2({
        width: '100%',
        tags:[{id: 'color', text: "red"}, {id : 'color', text: "green"},{id : 'color', text:"blue"}],
        tokenSeparators: [";"," "]
      });

      $('#whatwhat').select2({
          createSearchChoice: function(term){ return ''; },
          width: '100%',
          tags: []
      });

      $('#multiples').select2();

      $('#choices').select2({

          closeOnSelect: false,
          openOnEnter: false

        });

      $('div#add').click(function(){
        
        $('#whatwhat').select2('val', $('#choices').select2('val'));

      })


    </script
  </body>
</html>
