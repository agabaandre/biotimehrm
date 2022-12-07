 <!-- Breadcome End-->
 <?php
           $user=$this->session->get_userdata();
          
         
           if($user['role']=='sadmin'){
            $user='';
            
           }
           elseif($user['role']=='Department Admin'){
            $user='';

           }
           else{
            $user=$user['ihris_pid'];
           }

           $checkins=Modules::run('workshops/get_checkins',$user);
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
                                        <h1>Out of Station <span class="table-project-n"></span> Check In </h1>
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
                                                    <th data-field="email" data-editable="false">Date</th>
                                                 
                                                    <th data-field="complete">Request Details</th>
                                                    <th data-field="task" data-editable="false">Location</th>
                                                      </tr>
                                            </thead>
                                            <tbody>
                                                <?php $i=1; foreach ($checkins as $checkin) { 
                                                     
                                                    ?>
                                                <tr>
                                                  
                                                    <td><?php echo $i++; ?></td>
                                                    <td><?php $request=$checkin->request_id; echo $checkin->surname. " ". $checkin->firstname." ".$checkin->othername; ?> </td>
                                                    <td><?php echo $checkindate=$checkin->date; ?></td>
                                                   
                                                    <td><?php echo ( Modules::run('workshops/getRequests',$request)); ?></td>
                                                    <td><?php if (!empty($checkin->url)){

                                                     echo "<b>Cordinates: </b>".$location=$checkin->location."<br>";
                                                     echo "<b>Street: </b>".$location=$checkin->street;
                                                     echo "<b>City: </b>".$location=$checkin->city;
                                                     echo "<b>Region: </b>".$location=$checkin->region;
                                                     echo "<b>Country: </b>".$location=$checkin->country;
                                                                      $map=$checkin->url;
                                                    }
                                                    else{
                                                         $location=$checkin->location;
                                                         $location = (explode(",",$location));
                                                         $latitude=$location[0];
                                                         $longitude=$location[1]; 
                                                  
                                                         $geocodeFromLatLong = file_get_contents( 'http://www.mapquestapi.com/geocoding/v1/reverse?key=R3OTkrmAT5GI0AxbMNWWwGHodAVz0Sjl&location='.trim($latitude).','.trim($longitude).'&includeRoadMetadata=true&includeNearestIntersection=true'); 
                                                         
                                                         $output = json_decode($geocodeFromLatLong);
                                                              $entry_id=$checkin->entry_id;
                                                               $url = $output->results[0]->locations[0]->mapUrl;
                                                              echo "Cordinates: ".$location=$checkin->location."<br>";
                                                              echo "Street: ". $street = $output->results[0]->locations[0]->street."<br>";
                                                              echo "City: ". $city = $output->results[0]->locations[0]->adminArea5."<br>";
                                                              echo "Region: ".$region = $output->results[0]->locations[0]->adminArea3."<br>";
                                                              echo "Country: ". $country = $output->results[0]->locations[0]->adminArea1."<br>";
                                                              if(!empty($url)){
                                                                $checkins=Modules::run('workshops/updatemapData',$entry_id,$url,$street,$city,$region,$country);
                                                              }

                                                    }
                                                       
                                                    ?> 
                                                    </td>
                                                
                                                  
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