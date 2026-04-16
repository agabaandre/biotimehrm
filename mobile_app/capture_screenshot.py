import subprocess
import time
import os
import numpy as np
from PIL import Image
import shutil

def capture_screenshot(filename):
    subprocess.run(["adb", "exec-out", "screencap", "-p", ">", filename], shell=True, check=True)

def compare_images(image1, image2, threshold):
    # Open images and convert to grayscale
    img1 = Image.open(image1).convert('L')
    img2 = Image.open(image2).convert('L')

    # Convert images to numpy arrays
    arr1 = np.array(img1)
    arr2 = np.array(img2)

    # Calculate the mean absolute difference
    diff = np.mean(np.abs(arr1 - arr2))

    # Normalize the difference
    max_diff = 255  # maximum possible difference for 8-bit images
    similarity = 1 - (diff / max_diff)

    return similarity < threshold, similarity

def main():
    screenshot_dir = "screenshots"
    if not os.path.exists(screenshot_dir):
        os.makedirs(screenshot_dir)

    interval = 1  # Capture a screenshot every second
    duration = 900  # Run for 15 minutes
    similarity_threshold = 0.95  # Adjust this value to change sensitivity

    start_time = time.time()
    screenshot_count = 0
    last_screenshot = None

    while time.time() - start_time < duration:
        current_screenshot = os.path.join(screenshot_dir, f"temp_screenshot_{int(time.time())}.png")
        try:
            capture_screenshot(current_screenshot)
        except subprocess.CalledProcessError:
            print("Failed to capture screenshot. Is the device connected?")
            time.sleep(interval)
            continue

        if last_screenshot is not None and os.path.exists(last_screenshot):
            has_changed, similarity = compare_images(last_screenshot, current_screenshot, similarity_threshold)
            if has_changed:
                screenshot_count += 1
                new_filename = os.path.join(screenshot_dir, f"screenshot_{screenshot_count}.png")
                shutil.move(current_screenshot, new_filename)
                print(f"Screen changed! Similarity: {similarity:.4f}. Saved as: {new_filename}")
            else:
                print(f"No significant change detected. Similarity: {similarity:.4f}")
                os.remove(current_screenshot)
        else:
            screenshot_count += 1
            new_filename = os.path.join(screenshot_dir, f"screenshot_{screenshot_count}.png")
            shutil.move(current_screenshot, new_filename)
            print(f"Initial screenshot saved as: {new_filename}")

        last_screenshot = new_filename if screenshot_count > 0 else current_screenshot
        time.sleep(interval)

    print(f"Captured {screenshot_count} screenshots with changes in {screenshot_dir}")

    # Clean up any remaining temporary screenshots
    for file in os.listdir(screenshot_dir):
        if file.startswith("temp_screenshot_"):
            os.remove(os.path.join(screenshot_dir, file))

if __name__ == "__main__":
    main()