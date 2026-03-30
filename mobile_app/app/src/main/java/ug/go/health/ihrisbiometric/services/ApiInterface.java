package ug.go.health.ihrisbiometric.services;

import okhttp3.MultipartBody;
import okhttp3.RequestBody;
import okhttp3.ResponseBody;
import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.DELETE;
import retrofit2.http.GET;
import retrofit2.http.Multipart;
import retrofit2.http.POST;
import retrofit2.http.PUT;
import retrofit2.http.Part;
import retrofit2.http.Path;
import retrofit2.http.Query;
import ug.go.health.ihrisbiometric.models.ClockHistory;
import ug.go.health.ihrisbiometric.models.AllFacilitiesListResponse;
import ug.go.health.ihrisbiometric.models.CadresListResponse;
import ug.go.health.ihrisbiometric.models.DistrictsListResponse;
import ug.go.health.ihrisbiometric.models.FaceEmbeddingDownloadResponse;
import ug.go.health.ihrisbiometric.models.FaceEmbeddingUploadRequest;
import ug.go.health.ihrisbiometric.models.FaceUploadResponse;
import ug.go.health.ihrisbiometric.models.FacilityListResponse;
import ug.go.health.ihrisbiometric.models.FingerprintDownloadResponse;
import ug.go.health.ihrisbiometric.models.FingerprintUploadRequest;
import ug.go.health.ihrisbiometric.models.FingerprintUploadResponse;
import ug.go.health.ihrisbiometric.models.JobsListResponse;
import ug.go.health.ihrisbiometric.models.LoginRequest;
import ug.go.health.ihrisbiometric.models.LoginResponse;
import ug.go.health.ihrisbiometric.models.NotificationListResponse;
import ug.go.health.ihrisbiometric.models.OutOfStationRequest;
import ug.go.health.ihrisbiometric.models.OutOfStationResponse;
import ug.go.health.ihrisbiometric.models.ReasonsListResponse;
import ug.go.health.ihrisbiometric.models.StaffListResponse;
import ug.go.health.ihrisbiometric.models.StaffRecord;

public interface ApiInterface {

    // Get list of Notifications
    @GET("notifications_list")
    Call<NotificationListResponse> getNotificationList();

    // Get list of staff records
    @GET("staff_list")
    Call<StaffListResponse> getStaffList();

    // Get a single staff record by id
    @GET("staff_details/{id}")
    Call<StaffRecord> getStaffById(@Path("id") int id);

    // Login
    @POST("login")
    Call<LoginResponse> login(@Body LoginRequest request);

    // Get Staff List by FacilityName using retrofit
    @GET("staff_list")
    Call<StaffListResponse> getStaffListByFacilityName(@Query("facility_name") String facilityName);

    // Get List of facilities
    @GET("facilities")
    Call<FacilityListResponse> getFacilities();

    // Sync Staff Record to remote database
    @POST("enroll_user")
    Call<StaffRecord> syncStaffRecord(@Body StaffRecord staffRecord);

    @POST("clock_user")
    Call<ClockHistory> syncClockHistory(@Body ClockHistory clockHistory);

    @Multipart
    @POST("request")
    Call<OutOfStationResponse> submitOutOfStationRequest(
            @Part("startDate") RequestBody startDate,
            @Part("endDate") RequestBody endDate,
            @Part("reason") RequestBody reason,
            @Part("comments") RequestBody comments,
            @Part MultipartBody.Part document
    );

    @POST("staff/create")
    Call<StaffRecord> createStaff(@Body StaffRecord staffRecord);

    @PUT("staff/update/{id}")
    Call<StaffRecord> updateStaff(@Path("id") int id, @Body StaffRecord staffRecord);

    @DELETE("staff/delete/{id}")
    Call<ResponseBody> deleteStaff(@Path("id") int id);

    // Upload a fingerprint template to the server
    @POST("upload_fingerprint")
    Call<FingerprintUploadResponse> uploadFingerprint(@Body FingerprintUploadRequest request);

    // Download fingerprint templates for a facility
    @GET("fingerprints")
    Call<FingerprintDownloadResponse> getFingerprints(@Query("facility_id") String facilityId);

    // Upload a face embedding to the server
    @POST("upload_face_embedding")
    Call<FaceUploadResponse> uploadFaceEmbedding(@Body FaceEmbeddingUploadRequest request);

    // Download face embeddings for a facility
    @GET("face_embeddings")
    Call<FaceEmbeddingDownloadResponse> getFaceEmbeddings(@Query("facility_id") String facilityId);

    // Get list of reasons for leave/absence requests
    @GET("reasons")
    Call<ReasonsListResponse> getReasons();

    // Get list of employee cadres
    @GET("cadres")
    Call<CadresListResponse> getCadres();

    // Get list of districts
    @GET("districts")
    Call<DistrictsListResponse> getDistricts();

    // Get full list of facilities with details
    @GET("all_facilities")
    Call<AllFacilitiesListResponse> getAllFacilities();

    // Get list of employee jobs
    @GET("jobs")
    Call<JobsListResponse> getJobs();

}
