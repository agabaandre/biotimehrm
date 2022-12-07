<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<title>leave Application</title>

</head>
<body>
    
<header>

</header>

<style type="text/css">
    
    .form{
       
        padding-top: 0.3%;
        font-size: 14px;
        font-family: "Times New Roman", Times, serif;

    }

    .content{
        padding: 1%;
    }


    input{
       border: 0px;
       padding: 0.8%;
       width:300px;
       border-top:none;
       float: auto;
       min-width: 100%;
      
       border-right:none;
       border-left:none;
       border-bottom: 1px #000 dotted;
    }

    .text-input:focus{
        border-bottom: 2px green solid;
    }


    label{
        padding-top: 1.8%;
    }

    .das-btn{

        padding-top: 1%;
        padding-bottom: 1%;
        padding-right: 2%;
        padding-left: 2%;
        min-width: 40%;
        min-height: 40px;
        margin-top: 20px;
        margin-bottom: 5px;
        margin-right: 1%;
        text-align: center;


    }
    .das-btn:hover{

        border: 2px #eee solid;
        background-color: maroon;
        color: #fff;
    }

    .heading{
        padding-left:2%; 
        min-height: 50px; 
        min-width: 100%;
        background-color: #000; 
        color: #fff;
        margin-bottom:2%; 
        padding-top: 0.01%;
    }

    label{
        padding-left: 0.5%;
        text-transform: capitalize;
    }
    .row{

        margin-top: 5px;
    }
    hr{
	background-color: #fff;
	border-top: 2px dotted #8c8b8b;
}
@media screen and (max-width: 420px){
    
    .infom{
        display:none;
    }
  
}

.col-50{
    width: 48%;
    float: left;
    margin-right:2%;
}

.half{
     width: 48%;
}
hr {
	background-color: #fff;
	border-top: 2px dotted #8c8b8b;
}

</style>
	<section class="content">
			<div class="content-inner">
				<div class="container">
					
	
<div class="col-md-8 datform">
    <div class="content">
    <div class="row">
    <div class="panel" style="margin-top:-50px;">
    <p>The Uganda Public Service Standing Orders&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b style="text-align:right;">Appendix C-2</b></p>
  <hr style="color:black;">
    <p style="text-align:right; font-weight:bold;">PSF 12</P>
        
        
        <h2 style="text-align:center;">APPLICATION FOR LEAVE</h2>       
         <h3 style="text-align:justify; line-height:130%;">
        <p><b>Note:</b>To be addressed to the responsible Office/Head of Department/Head of Division</p>
        </h3>
        
        
        <span class="text-center" style="padding-bottom:3px; font-size:large; color:green;"></span>
        
            <form class="form" role="form" >
                            
                    <div class="row heading" style="">
                            <h4> SECTION I</h4>
                        </div>
                        <div class="row half">
                            <div class="col-md-6">
                                <label >To:</label>
                                <span style="width:300px;text-style:underline;"><hr></span>
                            </div></div>
                        <div class="row half">
                            <div class="col-md-6">
                                <label >Thru:</label>
                                <span style="width:300px;text-style:underline;"><hr></span>
                            </div>
                        </div>
                        <div class="row half">
                            <div class="col-md-6 ">
                                <label >Thru:</label>
                                <span style="width:300px;text-style:underline;"><hr></span>
                            </div>
                        </div>
                        <div class="row col-50">
                            <div class="col-md-6">
                                <label >Name:</label>
                                <span style="width:300px;text-style:underline;"><?php echo $employee->surname." ".$employee->firstname." ".$employee->othername; ?><hr></span>
                                
                            </div>
                        </div>

                        <div class="row col-50">
                            <div class="col-md-6">
                                <label >Designation:</label>
                                <span style="width:300px;text-style:underline;"><?php echo $employee->job; ?><hr></span>
                            </div>
                        </div>
                         <div class="row half">
                            <div class="col-md-6">
                                <label >Department:</label>
                                <span style="width:300px;text-style:underline;"><?php echo $employee->department; ?><hr></span>
                                
                            </div>
                        </div>

                       

                         <div class="row">
                            <div class="col-md-6">
                                <label >Leave Applied for (Days p.m)</label>
                                <span style="width:300px;text-style:underline;"><?php echo $employee->job; ?><hr></span>
                    
                            </div>
                        </div>

                         <div class="row">
                            <div class="col-md-6">
                                <label >Leave Address /Telephone/ Email</label>
                                <span style="width:500px;text-style:underline;"><hr></span>
                      
                            </div>
                        </div>


                        <div class="row col-50">
                            <div class="col-md-6">
                              
                            <span style="width:300px;text-style:underline;"><hr></span>
                      

                                <label >Date</label>
                            </div>
                        </div>

                        <div class="row col-50">
                            <div class="col-md-6">
                              
                                <span style="width:300px;text-style:underline;"><hr></span>
                      
                                <label >Signature of Officer</label>

                                <hr style="border:2px dotted red; width:200px; margin-top:10px;">
                            </div>
                        </div>


                        <br>
                        <br>
                        
                        <div class="row heading" style="margin-top: 5px;" >
                            <h4>SECTION II: To be completed by Head of Human resource</h4>
                        </div>

                        <div class="row">
                            <span style="padding-left: 20%; font-weight: bold;">COMPUTATION OF LEAVE</span>
                        </div>

                        <div class="row half">
                            <div class="col-md-6">
                                <label >Leave due in (year)</label>
                                <input type="text" class="text-input" name=""  style="width:40px; padding:100px;">
                            </div>
                        </div>
                         <div class="row half">
                            <div class="col-md-6">
                                <label >Less leave taken</label>
                                <input type="text" class="text-input" name=""  style="width:40px; padding:100px;">
                           
                            </div>
                        </div>
                         <div class="row half">
                            <div class="col-md-6">
                                <label >Balance</label>
                                <input type="text" class="text-input" name=""  style="width:40px; padding:100px;">
                           
                            </div>
                        </div>

                        <div class="row">
                            <span style="font-weight: bold;">LEAVE AS COMPUTED ABOVE RECOMMENDED/APPROVED</span>
                            <p>This application is in accordance with leave roster. Computation checked and leave recorded by</p>
                            <br>
                        </div>

                         <div class="row col-50">
                            <div class="col-md-6">
                                <label >Head of Human Resource</label>
                                <span style="width:300px;text-style:underline;"><hr></span>
                            </div>
                        </div>

                         <div class="row col-50">
                            <div class="col-md-6">
                                <label >Date</label>
                                <span style="width:300px;text-style:underline;"><hr></span>
                            </div>
                        </div>

                        <br><br>
                      



                        <div class="row heading" style="margin-top: 5px;" >
                            <h4>SECTION III: To be completed by Head of Human resource</h4>
                        </div>


                        <div class="row ">
                            <div class="col-md-6">
                                <label >To</label>
                                <span style="width:300px;text-style:underline;"><hr></span>
                            </div>
                        </div>
                         
                         <div class="row col-50">
                            <div class="col-md-6">
                                <label >Your applcation for leave from </label>
                                <span style="width:300px;text-style:underline;"><?php echo $employee->dateFrom; ?><hr></span>
                                                            </div>
                        </div>

                          <div class="row col-50">
                            <div class="col-md-6">
                                <label >To </label>
                                <span style="width:300px;text-style:underline;"><?php echo $employee->dateTo; ?><hr></span>
                                                        </div>
                        </div>

                         <div class="row half">
                            <div class="col-md-6">
                                <label>Is approved/not approved (reasons for not approving must be given)</label>
                            </div>
                        </div>

                        <br><br><br>


                         <div class="row col-50">
                            <div class="col-md-6">
                                <label >Signature of responsible officer</label>
                                <br>
                                <span style="width:300px;text-style:underline;"><hr></span>
                              
                            </div>
                        </div>

                         <div class="row col-50">
                            <div class="col-md-6">
                                <label >Date</label>
                                <br>
                                <span style="width:300px;text-style:underline;"><hr></span>
                              
                            </div>
                        </div>


                        <br><br><br><br>

                          <div class="row half">
                            <div class="col-md-6">
                                <label >Name</label>
                                <br>
                                <span style="width:300px;text-style:underline;"><hr></span>
                              
                            </div>
                        </div>
                          <div class="row half">
                            <div class="col-md-6">
                                <label >Designation</label>
                                <br>
                                <span style="width:300px;text-style:underline;"><?php echo $employee->job; ?><hr></span>
                            </div>
                        </div>
                    <div class="clearfix"></div>

            
                </div>
            </form>

        </div>
    </div>
  </div>


</div>
</div>
</div>
</div>
		
	</div>

	
</body>
</html>
