package ug.go.health.ihrisbiometric.utils;

public class FrameMetadata {
    private final int imageHeight;
    private final int imageRotation;
    private final int imageWidth;

    public int getImageWidth() {
        return this.imageWidth;
    }

    public int getImageHeight() {
        return this.imageHeight;
    }

    public int getImageRotation() {
        return this.imageRotation;
    }

    private FrameMetadata(int width, int height, int rotation) {
        this.imageWidth = width;
        this.imageHeight = height;
        this.imageRotation = rotation;
    }

    /* loaded from: C:\Users\WORK\Desktop\hrm\classes3.dex */
    public static class Builder {
        private int imageHeight;
        private int imageRotation;
        private int imageWidth;

        public Builder setImageWidth(int width) {
            this.imageWidth = width;
            return this;
        }

        public Builder setImageHeight(int height) {
            this.imageHeight = height;
            return this;
        }

        public Builder setImageRotation(int rotation) {
            this.imageRotation = rotation;
            return this;
        }

        public FrameMetadata build() {
            return new FrameMetadata(this.imageWidth, this.imageHeight, this.imageRotation);
        }
    }
}
