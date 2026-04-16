package ug.go.health.library;

/**
 * Encapsulates the result of a scanner operation.
 */
public class ScannerResult {
    public enum Type {
        SUCCESS,
        FAILURE,
        IN_PROGRESS,
        ERROR,
        WAITING_FOR_FINGER
    }

    private final Type type;
    private final short commandCode;
    private final String message;
    private final byte[] data;
    private final int value;

    public ScannerResult(Type type, short commandCode, String message, byte[] data, int value) {
        this.type = type;
        this.commandCode = commandCode;
        this.message = message;
        this.data = data;
        this.value = value;
    }

    public Type getType() { return type; }
    public short getCommandCode() { return commandCode; }
    public String getMessage() { return message; }
    public byte[] getData() { return data; }
    public int getValue() { return value; }

    public static ScannerResult success(short commandCode, String message, int value, byte[] data) {
        return new ScannerResult(Type.SUCCESS, commandCode, message, data, value);
    }

    public static ScannerResult failure(short commandCode, String message) {
        return new ScannerResult(Type.FAILURE, commandCode, message, null, -1);
    }

    public static ScannerResult error(short commandCode, String message) {
        return new ScannerResult(Type.ERROR, commandCode, message, null, -1);
    }

    public static ScannerResult inProgress(short commandCode, String message) {
        return new ScannerResult(Type.IN_PROGRESS, commandCode, message, null, -1);
    }

    public static ScannerResult waiting(short commandCode, String message) {
        return new ScannerResult(Type.WAITING_FOR_FINGER, commandCode, message, null, -1);
    }
}
