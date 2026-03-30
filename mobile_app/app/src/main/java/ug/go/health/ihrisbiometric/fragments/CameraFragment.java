package ug.go.health.ihrisbiometric.fragments;

import android.app.AlertDialog;
import android.content.Context;
import android.content.pm.PackageManager;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.ImageFormat;
import android.graphics.Matrix;
import android.hardware.camera2.CameraAccessException;
import android.hardware.camera2.CameraCharacteristics;
import android.hardware.camera2.CameraManager;
import android.location.Location;
import android.location.LocationManager;
import android.media.Image;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.util.Base64;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.annotation.OptIn;
import androidx.camera.core.CameraSelector;
import androidx.camera.core.ExperimentalGetImage;
import androidx.camera.core.ImageAnalysis;
import androidx.camera.core.ImageCapture;
import androidx.camera.core.ImageCaptureException;
import androidx.camera.core.ImageProxy;
import androidx.camera.core.Preview;
import androidx.camera.core.resolutionselector.ResolutionSelector;
import androidx.camera.core.resolutionselector.ResolutionStrategy;
import androidx.camera.lifecycle.ProcessCameraProvider;
import androidx.camera.view.PreviewView;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import androidx.fragment.app.Fragment;
import androidx.lifecycle.LifecycleOwner;
import androidx.lifecycle.ViewModelProvider;

import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.tasks.OnSuccessListener;
import com.google.common.util.concurrent.ListenableFuture;

import org.opencv.android.Utils;
import org.opencv.core.CvType;
import org.opencv.core.Mat;
import org.opencv.core.Size;
import org.opencv.imgproc.Imgproc;

import java.io.ByteArrayOutputStream;
import java.nio.ByteBuffer;
import java.util.Date;
import java.util.concurrent.ExecutionException;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

import ug.go.health.ihrisbiometric.R;
import ug.go.health.ihrisbiometric.models.ClockHistory;
import ug.go.health.ihrisbiometric.models.StaffRecord;
import ug.go.health.ihrisbiometric.services.DbService;
import ug.go.health.ihrisbiometric.services.FaceScanner;
import ug.go.health.ihrisbiometric.models.FaceScannerResult;
import ug.go.health.ihrisbiometric.utils.BitmapUtils;
import ug.go.health.ihrisbiometric.utils.ImageConverter;
import ug.go.health.ihrisbiometric.viewmodels.HomeViewModel;
import android.Manifest;

public class CameraFragment extends Fragment {

    private static final String TAG = CameraFragment.class.getSimpleName();
    private PreviewView previewView;
    private Button enrollButton;
    private ImageButton toggleCameraButton;
    private ImageView faceBoxFrame;
    private TextView faceStatusTextView;
    private ExecutorService cameraExecutor;

    private ImageCapture imageCapture;
    private ImageAnalysis imageAnalysis;
    private int lensFacing = CameraSelector.LENS_FACING_FRONT;

    private FaceScanner faceScanner;
    private FaceScannerResult faceScannerResult;

    private HomeViewModel viewModel;
    private String actionType;

    private ProcessCameraProvider cameraProvider;

    private ImageConverter imageConverter;

    private StaffRecord selectedStaff;

    private DbService dbService;

    private FusedLocationProviderClient fusedLocationClient;

    private boolean isProcessing = false;
    private boolean isFragmentAttached = false;

    private boolean hasMultipleCameras = false;

    @Override
    public void onAttach(@NonNull Context context) {
        super.onAttach(context);
        isFragmentAttached = true;
    }

    @Override
    public void onDetach() {
        super.onDetach();
        isFragmentAttached = false;
        if (cameraExecutor != null) {
            cameraExecutor.shutdownNow();
        }
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        viewModel = new ViewModelProvider(requireActivity()).get(HomeViewModel.class);
        imageConverter = new ImageConverter(getContext());
        faceScanner = new FaceScanner();
        faceScannerResult = new FaceScannerResult();
        faceScanner.initEngine(getContext());
        dbService = new DbService(getContext());
        fusedLocationClient = LocationServices.getFusedLocationProviderClient(getContext());

        // Check if the device has multiple cameras
        hasMultipleCameras = checkForMultipleCameras();
    }

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_camera, container, false);
        previewView = view.findViewById(R.id.preview_view);
        enrollButton = view.findViewById(R.id.action_button);
        toggleCameraButton = view.findViewById(R.id.toggle_camera_button);
        faceBoxFrame = view.findViewById(R.id.face_box_frame);
        faceStatusTextView = view.findViewById(R.id.face_status);
        actionType = viewModel.getActionType().getValue();

        // Show or hide the toggle camera button based on available cameras
        if (!hasMultipleCameras) {
            toggleCameraButton.setVisibility(View.GONE);
            // Set default camera to back if only one camera is available
            lensFacing = CameraSelector.LENS_FACING_BACK;
        }

        return view;
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        cameraExecutor = Executors.newSingleThreadExecutor();
        startCamera();
        setupUI();

        viewModel.getSelectedStaff().observe(getViewLifecycleOwner(), staff -> {
            if (staff != null) {
                this.selectedStaff = staff;
            }
        });
    }

    private void setupUI() {
        if ("enroll".equals(actionType)) {
            enrollButton.setVisibility(View.VISIBLE);
            enrollButton.setOnClickListener(v -> {
                enrollButton.setEnabled(false);
                enrollButton.setText("Enrolling...");
                captureImage();
            });
            updateFaceStatus("Please position your face and click 'Enroll'");
        } else if ("clock".equals(actionType)) {
            enrollButton.setVisibility(View.GONE);
            updateFaceStatus("Please position your face for clocking");
        }

        if (hasMultipleCameras) {
            toggleCameraButton.setOnClickListener(v -> toggleCamera());
        }
    }

    private void startCamera() {
        ListenableFuture<ProcessCameraProvider> cameraProviderFuture = ProcessCameraProvider.getInstance(requireContext());

        cameraProviderFuture.addListener(() -> {
            try {
                cameraProvider = cameraProviderFuture.get();
                bindCameraUseCases();
            } catch (ExecutionException | InterruptedException e) {
                Log.e(TAG, "Error starting camera", e);
                updateFaceStatus("Error starting camera: " + e.getMessage());
            }
        }, ContextCompat.getMainExecutor(requireContext()));
    }

    private void bindCameraUseCases() {
        if (cameraProvider == null) {
            return;
        }

        Preview preview = new Preview.Builder().build();
        CameraSelector cameraSelector = new CameraSelector.Builder()
                .requireLensFacing(lensFacing)
                .build();

        preview.setSurfaceProvider(previewView.getSurfaceProvider());

        try {
            cameraProvider.unbindAll();

            if ("enroll".equals(actionType)) {
                imageCapture = new ImageCapture.Builder()
                        .setCaptureMode(ImageCapture.CAPTURE_MODE_MINIMIZE_LATENCY)
                        .build();
                cameraProvider.bindToLifecycle((LifecycleOwner) this, cameraSelector, preview, imageCapture);
            } else if ("clock".equals(actionType)) {
                imageCapture = new ImageCapture.Builder()
                        .setCaptureMode(ImageCapture.CAPTURE_MODE_MINIMIZE_LATENCY)
                        .build();
                imageAnalysis = new ImageAnalysis.Builder()
                        .setBackpressureStrategy(ImageAnalysis.STRATEGY_KEEP_ONLY_LATEST)
                        .setOutputImageFormat(ImageAnalysis.OUTPUT_IMAGE_FORMAT_YUV_420_888) // Maintain output format as YUV for analysis
                        .setResolutionSelector(new ResolutionSelector.Builder()
                                .setResolutionStrategy(ResolutionStrategy.HIGHEST_AVAILABLE_STRATEGY) // Use the highest available resolution
                                .build())
                        .build();
                imageAnalysis.setAnalyzer(cameraExecutor, this::analyzeImage);
                cameraProvider.bindToLifecycle((LifecycleOwner) this, cameraSelector, preview, imageCapture, imageAnalysis);
            }
        } catch (Exception e) {
            Log.e(TAG, "Use case binding failed", e);
            updateFaceStatus("Failed to bind camera use cases");
        }
    }

    private void toggleCamera() {
        if (cameraProvider == null || !hasMultipleCameras) {
            return;
        }

        lensFacing = (lensFacing == CameraSelector.LENS_FACING_FRONT) ?
                CameraSelector.LENS_FACING_BACK : CameraSelector.LENS_FACING_FRONT;

        bindCameraUseCases();
    }

    private void captureImage() {
        if (imageCapture == null) return;

        imageCapture.takePicture(cameraExecutor,
                new ImageCapture.OnImageCapturedCallback() {
                    @Override
                    public void onCaptureSuccess(@NonNull ImageProxy image) {
                        processImage(image, true);
                    }

                    @Override
                    public void onError(@NonNull ImageCaptureException exception) {
                        Log.e(TAG, "Image capture failed", exception);
                        updateFaceStatus("Image capture failed: " + exception.getMessage());
                    }
                });
    }

    private void analyzeImage(ImageProxy image) {
        if (!isFragmentAttached) {
            image.close();
            return;
        }

        if (isProcessing) {
            image.close();
            return;
        }

        isProcessing = true;
        captureImageForAnalysis();
    }

    private void captureImageForAnalysis() {
        if (imageCapture == null) return;

        imageCapture.takePicture(cameraExecutor,
                new ImageCapture.OnImageCapturedCallback() {
                    @Override
                    public void onCaptureSuccess(@NonNull ImageProxy image) {
                        processImage(image, false);
                    }

                    @Override
                    public void onError(@NonNull ImageCaptureException exception) {
                        Log.e(TAG, "Image capture failed", exception);
                        updateFaceStatus("Image capture failed: " + exception.getMessage());
                        isProcessing = false;
                    }
                });
    }

    @OptIn(markerClass = ExperimentalGetImage.class)
    private final Handler debounceHandler = new Handler(Looper.getMainLooper());
    private final Runnable debounceRunnable = () -> isProcessing = false;

    @OptIn(markerClass = ExperimentalGetImage.class)
    private void processImage(ImageProxy imageProxy, boolean isEnrollment) {
        if (!isFragmentAttached) {
            imageProxy.close();
            isProcessing = false;
            return;
        }

        Image image = imageProxy.getImage();

        if (image == null) {
            imageProxy.close();
            isProcessing = false;
            return;
        }

        if (image.getFormat() != ImageFormat.JPEG && image.getFormat() != ImageFormat.YUV_420_888) {
            imageProxy.close();
            isProcessing = false;
            return;
        }

        Bitmap bitmap = null;

        if (image.getFormat() == ImageFormat.JPEG) {
            ByteBuffer buffer = image.getPlanes()[0].getBuffer();
            byte[] bytes = new byte[buffer.remaining()];
            buffer.get(bytes);
            bitmap = BitmapFactory.decodeByteArray(bytes, 0, bytes.length);
        } else if (image.getFormat() == ImageFormat.YUV_420_888) {
            bitmap = BitmapUtils.getBitmap(imageProxy);
        }

        if (bitmap == null) {
            imageProxy.close();
            isProcessing = false;
            return;
        }

        Matrix matrix = new Matrix();
        matrix.postRotate(imageProxy.getImageInfo().getRotationDegrees());

        Bitmap rotatedBitmap = Bitmap.createBitmap(bitmap, 0, 0, bitmap.getWidth(), bitmap.getHeight(), matrix, true);

        Mat mRgbFrame = new Mat(rotatedBitmap.getHeight(), rotatedBitmap.getWidth(), CvType.CV_8UC3);
        Utils.bitmapToMat(rotatedBitmap, mRgbFrame);
        Imgproc.cvtColor(mRgbFrame, mRgbFrame, Imgproc.COLOR_BGR2RGB);

        FaceScannerResult result = new FaceScannerResult();
        int detectionResult = faceScanner.processImage(mRgbFrame, result);

        if (detectionResult != 1 || !result.faceInfo.isLive) {
            if (detectionResult != 1) {
                updateFaceStatus("No face detected. Please try again.");
            } else if (!result.faceInfo.isLive) {
                updateFaceStatus("Please use a live face. Try again.");
            }
            if (!isEnrollment) {
                isProcessing = false;
            }
            imageProxy.close();
            // TODO Re-enable after testing
             return;
        }

        if (isEnrollment) {
            enrollFace(mRgbFrame);
        } else {
            clockInOut(result);
        }

        imageProxy.close();
        debounceHandler.postDelayed(debounceRunnable, 1000); // 1000 milliseconds = 1 second
    }

    private void enrollFace(Mat mRgbFrame) {
        selectedStaff = viewModel.getSelectedStaff().getValue();
        if (selectedStaff != null) {
            String userId = selectedStaff.getIhrisPid();
            Log.d(TAG, "enrollFace: Enrolling user with info " + selectedStaff.toJson());
            String enrollmentStatus = faceScanner.registerFace(mRgbFrame, userId);

            if (enrollmentStatus.startsWith("SUCCESS")) {
                // Resize the Mat object to fit within 480 by 480 pixels dimensions
                int maxDimension = Math.max(mRgbFrame.cols(), mRgbFrame.rows());
                double scale = 480.0 / maxDimension;
                Mat resizedMat = new Mat();
                Size newSize = new Size(mRgbFrame.cols() * scale, mRgbFrame.rows() * scale);
                Imgproc.resize(mRgbFrame, resizedMat, newSize, 0, 0, Imgproc.INTER_AREA);

                Bitmap bitmap = Bitmap.createBitmap((int) newSize.width, (int) newSize.height, Bitmap.Config.ARGB_8888);
                Utils.matToBitmap(resizedMat, bitmap);
                ByteArrayOutputStream byteArrayOutputStream = new ByteArrayOutputStream();
                bitmap.compress(Bitmap.CompressFormat.JPEG, 90, byteArrayOutputStream);
                byte[] byteArray = byteArrayOutputStream.toByteArray();
                String base64Image = Base64.encodeToString(byteArray, Base64.DEFAULT);

                // Get current coordinates
                Location location = getCurrentLocation();
                if (location != null) {
                    selectedStaff.setLocation(new ug.go.health.ihrisbiometric.models.Location(location.getLatitude(), location.getLongitude()));
                }

                selectedStaff.setFaceEnrolled(true);
                selectedStaff.setFaceImage(base64Image);
                dbService.updateStaffRecordAsync(selectedStaff, success -> {
                    if (success) {
                        showSuccessDialog("Face Enrolled", "Staff successfully enrolled");
                    } else {
                        updateFaceStatus("Failed to update staff record");
                    }
                    requireActivity().runOnUiThread(() -> {
                        enrollButton.setEnabled(true);
                        enrollButton.setText("Enroll Staff");
                    });
                });
            } else {
                updateFaceStatus(enrollmentStatus);
            }
        } else {
            updateFaceStatus("Error: No staff selected for enrollment");
        }
    }

    private Location getCurrentLocation() {
        final Location[] currentLocation = {null};

        if (ActivityCompat.checkSelfPermission(getContext(), Manifest.permission.ACCESS_FINE_LOCATION)
                != PackageManager.PERMISSION_GRANTED && ActivityCompat.checkSelfPermission(getContext(),
                Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED) {
            return null; // Permission not granted, return null
        }

        fusedLocationClient.getLastLocation().addOnSuccessListener(new OnSuccessListener<Location>() {
            @Override
            public void onSuccess(Location location) {
                if(location != null) {
                    currentLocation[0] = location;
                }
            }
        });

        return currentLocation[0];
    }

    private void clockInOut(FaceScannerResult result) {
        Log.d(TAG, "clockInOut: " + result.faceInfo.toJson());
        boolean faceDetected = result.faceInfo.faceDetected;
        if (result.faceInfo.isEnrolled) {
            String ihrisPid = result.faceInfo.ihrisPID;
            dbService.getStaffRecordByihrisPIDAsync(ihrisPid, new DbService.Callback<StaffRecord>() {
                @Override
                public void onResult(StaffRecord staffRecord) {
                    if (staffRecord != null) {
                        dbService.getLastClockHistoryAsync(ihrisPid, new DbService.Callback<ClockHistory>() {
                            @Override
                            public void onResult(ClockHistory lastClockHistory) {
                                String clockStatus = (lastClockHistory != null && "CLOCK_IN".equals(lastClockHistory.getClockStatus())) ? "CLOCK_OUT" : "CLOCK_IN";

                                ClockHistory newClockHistory = new ClockHistory();
                                newClockHistory.setIhrisPID(ihrisPid);
                                newClockHistory.setName(staffRecord.getName());
                                newClockHistory.setClockTime(new Date());
                                newClockHistory.setClockStatus(clockStatus);
                                newClockHistory.setSynced(false);

                                dbService.saveClockHistoryAsync(newClockHistory, new DbService.Callback<Boolean>() {
                                    @Override
                                    public void onResult(Boolean success) {
                                        if (success) {
                                            updateFaceStatus(staffRecord.getName() + " successfully " + (clockStatus.equals("CLOCK_IN") ? "clocked in" : "clocked out") + ".");
                                            viewModel.setStatus(staffRecord.getName() + " successfully " + (clockStatus.equals("CLOCK_IN") ? "clocked in" : "clocked out") + ".");
                                        } else {
                                            updateFaceStatus("Failed to save clock record. Please try again.");
                                        }
                                        // Delay navigation to allow user to read the status
                                        new Handler(Looper.getMainLooper()).postDelayed(() -> navigateBack(), 2000);
                                    }
                                });
                            }
                        });
                    } else {
                        updateFaceStatus("Staff record not found. Please try again or contact support.");
                        new Handler(Looper.getMainLooper()).postDelayed(() -> navigateBack(), 2000);
                    }
                }
            });
        } else {
            updateFaceStatus("Face not recognized. Please try again or contact support.");
            new Handler(Looper.getMainLooper()).postDelayed(() -> navigateBack(), 2000);
        }
    }

    private void navigateBack() {
        if (isFragmentAttached) {
            requireActivity().runOnUiThread(() -> {
                if (isFragmentAttached) {
                    requireActivity().getSupportFragmentManager().popBackStack();
                }
            });
        }
    }

    private void showSuccessDialog(String title, String message) {
        if (isFragmentAttached) {
            new AlertDialog.Builder(requireContext())
                    .setTitle(title)
                    .setMessage(message)
                    .setPositiveButton("OK", (dialog, which) -> navigateBack())
                    .setCancelable(false)
                    .show();
        }
    }

    private void updateFaceStatus(String status) {
        if (isFragmentAttached) {
            requireActivity().runOnUiThread(() -> {
                if (isFragmentAttached && faceStatusTextView != null) {
                    faceStatusTextView.setText(status);
//                    new Handler(Looper.getMainLooper()).postDelayed(() -> {
//                        if (isFragmentAttached && faceStatusTextView != null) {
//                            faceStatusTextView.setText("");
//                        }
//                    }, 2000); // 5000 milliseconds = 5 seconds
                }
            });
        }
    }

    private boolean checkForMultipleCameras() {
        CameraManager cameraManager = (CameraManager) requireContext().getSystemService(Context.CAMERA_SERVICE);
        try {
            String[] cameraIds = cameraManager.getCameraIdList();
            if (cameraIds.length > 1) {
                for (String id : cameraIds) {
                    CameraCharacteristics characteristics = cameraManager.getCameraCharacteristics(id);
                    Integer facing = characteristics.get(CameraCharacteristics.LENS_FACING);
                    if (facing != null && facing == CameraCharacteristics.LENS_FACING_FRONT) {
                        return true;  // Device has both front and back cameras
                    }
                }
            }
        } catch (CameraAccessException e) {
            Log.e(TAG, "Failed to access camera", e);
        }
        return false;  // Device has only one camera (assumed to be back camera)
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        if (cameraExecutor != null) {
            cameraExecutor.shutdownNow();
        }
    }
}
