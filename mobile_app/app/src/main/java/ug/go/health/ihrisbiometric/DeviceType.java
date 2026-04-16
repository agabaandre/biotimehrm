package ug.go.health.ihrisbiometric;

public enum DeviceType {

    /**
     * The device is a fingerprint scanner
     * Example Usage: DeviceType.SCANNER
     *
     */

    SCANNER("scanner"),
    MOBILE("mobile");

    private String type;

    DeviceType(String type) {
        this.type = type;
    }

    public String getType() {
        return type;
    }

    public static DeviceType fromString(String type) {
        for (DeviceType deviceType : DeviceType.values()) {
            if (deviceType.getType().equals(type)) {
                return deviceType;
            }
        }
        return null;
    }

    public static DeviceType fromInt(int type) {
        for (DeviceType deviceType : DeviceType.values()) {
            if (deviceType.ordinal() == type) {
                return deviceType;
            }
        }
        return null;
    }


}
