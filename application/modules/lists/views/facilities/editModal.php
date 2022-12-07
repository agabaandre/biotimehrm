<!-- Default modal Size -->
<div class="modal fade" id="EditModal<?php echo $facility->id; ?>"  >
    <div class="modal-dialog modal-md" >
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel" style="text-align: center;"><b>Edit</b> <i><u><?php echo $facility->facility; ?></u></i> Facility</h4>
            </div>

             <form enctype="multipart/form-data" method="post" 
             action="<?php echo base_url(); ?>facilities/updateFacility">
                  <div class="modal-body"> 

                            <div class="form-group">
                            <input type="hidden" name="id" value="<?php echo $facility->id; ?>">
                                <label>District</label>
                                <select type="text" class="form-control select2" name="district_id" required>
                                <option disabled>Select ...</option>
                                <?php 
                                    $districts = Modules::run('lists/get_all_districts');
                                    foreach($districts as $district){ ?>
                                    <option value="<?php echo $district->id; ?>"><?php echo $district->name; ?></option>
                                <?php } ?>

                                </select>
                            </div>

                            <div class="form-group">
                                <label>Facility Name</label>
                                <input type="text" class="form-control" name="facility" required>
                            </div>
                            <div class="form-group">
                                <label>Code</label>
                                <input type="text" class="form-control" name="facility_id" required>
                            </div>
                            <div class="form-group">
                                <label>Instution Cateegories</label>
                                <select type="text" class="form-control select2" name="institution_cateegory" required>
                                <option value="">Select ...</option>
                                    
                                <?php if($district->institution_cateegory){ echo "Central Government";} ?>

                                    <option <?php if($district->institution_cateegory=="Central Government"){ echo "selected";} ?> 
                                            value="Central Government">Central Government</option>
                                    <option <?php if($district->institution_cateegory=="Local Government (LG)"){ echo "selected";} ?>
                                            value="Local Government (LG)">Local Government (LG)</option>
                                    <option <?php if($district->institution_cateegory=="Private for Profit (PFPs)"){ echo "selected";} ?>
                                            value="Private for Profit (PFPs)">Private for Profit (PFPs)</option>
                                    <option <?php if($district->institution_cateegory=="Private not for Profit (PNFPs)"){ echo "selected";} ?>
                                            value="Private not for Profit (PNFPs)">Private not for Profit (PNFPs)</option>
                                    <option <?php if($district->institution_cateegory=="Security Forces"){ echo "selected";} ?>
                                           value="Security Forces">Security Forces</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Instutution Types</label>
                                <select type="text" class="form-control select2" name="institution_type" required>
                                    <option value="">Select ...</option>
                                    <option <?php if($district->institution_type=="City"){ echo "selected";} ?>
                                            value="City">City</option>
                                    <option <?php if($district->institution_type=="Civil Society Organisations (CSO)"){ echo "selected";} ?>
                                            value="Civil Society Organisations (CSO)">Civil Society Organisations (CSO)</option>
                                    <option <?php if($district->institution_type=="District"){ echo "selected";} ?>
                                            value="District">District</option>
                                    <option <?php if($district->institution_type=="Ministry"){ echo "selected";} ?>
                                            value="Ministry">Ministry</option>
                                    <option <?php if($district->institution_type=="Municipality"){ echo "selected";} ?>
                                            value="Municipality">Municipality</option>
                                    <option <?php if($district->institution_type=="National Referral Hospital"){ echo "selected";} ?>
                                            value="National Referral Hospital">National Referral Hospital</option>
                                    <option <?php if($district->institution_type=="Regional Referral Hospital"){ echo "selected";} ?>
                                            value="Regional Referral Hospital">Regional Referral Hospital</option>
                                    <option <?php if($district->institution_type=="Specialised Facility"){ echo "selected";} ?>
                                            value="Specialised Facility">Specialised Facility</option>
                                    <option <?php if($district->institution_type=="UBTS"){ echo "selected";} ?>
                                            value="UBTS">UBTS</option>
                                    <option <?php if($district->institution_type=="UCBHCA"){ echo "selected";} ?>
                                            value="UCBHCA">UCBHCA</option>
                                    <option <?php if($district->institution_type=="UCMB"){ echo "selected";} ?>
                                            value="UCMB">UCMB</option>
                                    <option <?php if($district->institution_type=="UMMB"){ echo "selected";} ?>
                                            value="UMMB">UMMB</option>
                                    <option <?php if($district->institution_type=="UOMB"){ echo "selected";} ?>
                                            value="UOMB">UOMB</option>
                                    <option <?php if($district->institution_type=="UPMB"){ echo "selected";} ?>
                                            value="UPMB">UPMB</option>
                                    <option <?php if($district->institution_type=="Uganda Healthcare Federation (UHF)"){ echo "selected";} ?>
                                    <?php if($district->institution_type=="Uganda Healthcare Federation (UHF)"){ echo "selected";} ?>
                                            value="Uganda Healthcare Federation (UHF)">Uganda Healthcare Federation (UHF)</option>
                                    <option <?php if($district->institution_type=="Uganda Peoples Defence Force (UPDF)"){ echo "selected";} ?>
                                            value="Uganda Peoples Defence Force (UPDF)">Uganda Peoples Defence Force (UPDF)</option>
                                    <option <?php if($district->institution_type=="Uganda Police"){ echo "selected";} ?>
                                            value="Uganda Police">Uganda Police</option>
                                    <option <?php if($district->institution_type=="Uganda Prison Services"){ echo "selected";} ?>
                                    value="Uganda Prison Services">Uganda Prison Services</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Institution Level</label>
                                <select type="text" class="form-control select2" name="institution_level" required>
                                    <option value="">Select ...</option>
                                    <option <?php if($district->institution_level=="Primary School"){ echo "selected";} ?>
                                            value="Primary School">Primary School</option>
                                    <option <?php if($district->institution_level=="Secondary School"){ echo "selected";} ?>
                                            value="Secondary School">Secondary School</option>
                                    <option <?php if($district->institution_level=="Tertiary Instution"){ echo "selected";} ?>
                                            value="Tertiary Instution">Tertiary Instution</option>
                                    <option <?php if($district->institution_level=="University"){ echo "selected";} ?>
                                            value="University">University</option>
                                    <option <?php if($district->institution_level=="Blood Bank Main Office"){ echo "selected";} ?>
                                            value="Blood Bank Main Office">Blood Bank Main Office</option>
                                    <option <?php if($district->institution_level=="Blood Bank Regional Office"){ echo "selected";} ?>
                                            value="Blood Bank Regional Office">Blood Bank Regional Office</option>
                                    <option <?php if($district->institution_level=="City Health Office"){ echo "selected";} ?>
                                            value="City Health Office">City Health Office</option>
                                    <option <?php if($district->institution_level=="Clinic/ Medical Centre"){ echo "selected";} ?>
                                            value="Clinic/ Medical Centre">Clinic/ Medical Centre</option>
                                    <option <?php if($district->institution_level=="DHOs Office"){ echo "selected";} ?>
                                            value="DHOs Office">DHOs Office</option>
                                    <option <?php if($district->institution_level=="General Hospital"){ echo "selected";} ?>
                                            value="General Hospital">General Hospital</option>
                                    <option <?php if($district->institution_level=="HCII"){ echo "selected";} ?>
                                            value="HCII">HCII</option>
                                    <option <?php if($district->institution_level=="HCIII"){ echo "selected";} ?>
                                            value="HCIII">HCIII</option>
                                    <option <?php if($district->institution_level=="HCIV"){ echo "selected";} ?>
                                            value="HCIV">HCIV</option>
                                    <option <?php if($district->institution_level=="Medical Bureau Main Office"){ echo "selected";} ?>
                                            value="Medical Bureau Main Office">Medical Bureau Main Office</option>
                                    <option <?php if($district->institution_level=="Ministry"){ echo "selected";} ?>
                                            value="Ministry">Ministry</option>
                                    <option <?php if($district->institution_level=="Municipal Health Office"){ echo "selected";} ?>
                                            value="Municipal Health Office">Municipal Health Office</option>
                                    <option <?php if($district->institution_level=="National Referral Hospital"){ echo "selected";} ?>
                                            value="National Referral Hospital">National Referral Hospital</option>
                                    <option <?php if($district->institution_level=="Regional Referral Hospital"){ echo "selected";} ?>
                                            value="Regional Referral Hospital">Regional Referral Hospital</option>
                                    <option <?php if($district->institution_level=="Security Forces Main Office"){ echo "selected";} ?>
                                            value="Security Forces Main Office">Security Forces Main Office</option>
                                    <option <?php if($district->institution_level=="Specialised National Facility"){ echo "selected";} ?>
                                            value="Specialised National Facility">Specialised National Facility</option>
                                    <option <?php if($district->institution_level=="Town Council Office"){ echo "selected";} ?>
                                            value="Town Council Office">Town Council Office</option>
                                </select>
                            </div>
                    


                   </div>
                  <div class="modal-footer">

                   <button type="submit"  data-toggle="modal" class="btn btn-info waves-effect">Save Changes</button>
                   <a href="#" class="close btn" data-dismiss="modal">Close</a>
                  
                </div>
              </form>
            </div>
        </div>
    </div>

