 <html>
     <head>
         
         
 <style>
 form {
       width:100%;
       padding-top:8%;
}

.container{
    
    padding-left:34%;
    padding-right:30%;
    margin:0;
}


.select-btn{
    
    padding-top:0.5em;
    padding-bottom:0.5em;
    min-width:3em;
    text-align:center;
    border-radius:8%;
    background-color:#538e26;
    color:#fff;
     border:0;
}

.select-input{
    
    min-width:300px;
    padding:0.5em;
   
    
}
h1{
    color:grey;
}
</style>
         
     </head>

 <body>
 
 
 <form action="<? echo base_url('admin/selector'); ?>" method="post">
      
      <center><label><h1>SELECT FACILITY (<?=$_SESSION['district']?> DISTRICT)</h1></label></center>
 
  <div class="container">
   

   
    <select name="facility" class="select-input" required>
        
        <option>---SELECT FACILITY---</option>
        
         <?php foreach ($facilities as $facility) {
  ?>
  <option value="<?php echo $facility['facility_id']; ?>"><?php echo $facility['facility']; ?></option>

  <?php } ?>

        
        
    </select>

    <button type="submit" class="select-btn">Continue</button>
 
  </div>

  
</form> 


</body>
 </html>
 