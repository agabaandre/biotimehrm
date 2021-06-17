 <!-- Breadcome End-->
 <?php
           $user=$this->session->get_userdata();
          
         
           if($user['role']!='sadmin'){
            $user=$user['ihris_pid'];
           }

           $checkins=Modules::run('workshops/getDevices',$user);
           //print_r($checkins);
 ?>
            <!-- Static Table Start -->
            <div class="data-table-area mg-b-15">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="sparkline13-list shadow-reset">
                                <div class="sparkline13-hd">
                                    <div class="main-sparkline13-hd">
                                        <h1>Linked Devices <span class="table-project-n"></span>for Check In </h1>
                                        <div class="sparkline13-outline-icon">
                                            <span class="sparkline13-collapse-link"><i class="fa fa-chevron-up"></i></span>
                                            <span><i class="fa fa-wrench"></i></span>
                                            <span class="sparkline13-collapse-close"><i class="fa fa-times"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="sparkline13-graph">
                                    <div class="datatable-dashv1-list custom-datatable-overright">
                                        <div id="toolbar">
                                            <select class="form-control">
                                                <option value="">Export Basic</option>
                                                <option value="all">Export All</option>
                                                <option value="selected">Export Selected</option>
                                            </select>
                                        </div>
                                        <table id="table" data-toggle="table" data-pagination="true" data-search="true" data-show-columns="true" data-show-pagination-switch="true" data-show-refresh="true" data-key-events="true" data-show-toggle="true" data-resizable="true" data-cookie="true" data-cookie-id-table="saveId" data-show-export="true" data-click-to-select="true" data-toolbar="#toolbar">
                                            <thead>
                                                <tr>
                                
                                                    <th data-field="id">NO</th>
                                                    <th data-field="name" data-editable="false">Name</th>
                                                    <th data-field="email" data-editable="false">Device ID</th>
                                                    <th data-field="email" data-editable="false">Subscriber ID</th>
                                                 
                                                    <th data-field="complete">Action</th>
                                                    
                                                      </tr>
                                            </thead>
                                            <tbody>
                                                <?php $i=1; foreach ($checkins as $checkin) { 
                                                     
                                                    ?>
                                                <tr>
                                                  
                                                    <td><?php echo $i++; ?></td>
                                                    <td><?php $person=$checkin->personId; echo $checkin->surname. " ". $checkin->firstname." ".$checkin->othername; ?> </td>
                                                    <td><?php echo $checkindate=$checkin->deviceId; ?></td>
                                                    <td><?php echo $checkindate=$checkin->subscriberId; ?></td>
                                                    
                                                   
                                                    <td><a class="btn btn-sm btn-default" href="<?php echo base_url();?>workshops/unlinkDevices/<?php echo $person; ?>">Unlick</button></a>
                                                   
                                                
                                                  
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>