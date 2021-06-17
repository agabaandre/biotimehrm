

<!-- Main content -->
<section class="content">
     <div class="container-fluid">
       <!-- Main row -->

       <div class="row">
         
         <section class="col-lg-12 ">
           <!-- Custom tabs (Charts with tabs)-->
           <div class="card">
             <div class="card-header">
             
               <div class="card-tools">
              
               </div>
             </div><!-- /.card-header -->
          <div class="card-body">
          <?php $staff=Modules::run('employees/get_employees'); 
          
             print_r($staff);
          ?>
          
           </div>
         
         
         </section>
       </div>
       <!-- /.row (main row) -->
     </div><!-- /.container-fluid -->
   </section>
   <!-- /.content -->


   