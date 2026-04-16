package ug.go.health.ihrisbiometric.models;

import com.google.gson.annotations.Expose;
import com.google.gson.annotations.SerializedName;

import java.util.List;

public class FaceEmbeddingDownloadResponse {

    @SerializedName("status")
    @Expose
    private String status;

    @SerializedName("embeddings")
    @Expose
    private List<FaceEmbeddingRecord> embeddings;

    public String getStatus() {
        return status;
    }

    public List<FaceEmbeddingRecord> getEmbeddings() {
        return embeddings;
    }
}
