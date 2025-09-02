import os
import requests
import subprocess
import tempfile

def generate_thumbnail(video_path, output_dir='.'):
    """
    Generate a thumbnail from the video using ffmpeg.
    Extracts a frame at 1 second into the video.
    Requires ffmpeg to be installed on the system.
    """
    base_name = os.path.splitext(os.path.basename(video_path))[0]
    thumbnail_name = f"{base_name}.jpg"
    thumbnail_path = os.path.join(output_dir, thumbnail_name)
    try:
        subprocess.run([
            'ffmpeg',
            '-i', video_path,
            '-ss', '00:00:08.000',  # Take frame at 1 second
            '-vframes', '1',        # Single frame
            thumbnail_path,
            '-y'                    # Overwrite if exists
        ], check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        print(f"Thumbnail generated for {video_path} at: {thumbnail_path}")
        return thumbnail_path
    except subprocess.CalledProcessError as e:
        print(f"Failed to generate thumbnail for {video_path}: {e}")
        return None

def upload_video(login_url, upload_url, username, password, video_path):
    """
    Automate video upload to the website for a single video.
    - Logs in using provided credentials.
    - Generates thumbnail automatically.
    - Sets title to the video filename (without extension).
    - Sets description to empty.
    """
    # Create a session to maintain cookies
    session = requests.Session()

    # Step 1: Login
    login_data = {
        'username': username,
        'password': password
    }
    try:
        login_response = session.post(login_url, data=login_data)
        if login_response.status_code != 200 or 'login' in login_response.url.lower():
            print("Login failed. Check credentials or URL.")
            return False
    except requests.RequestException as e:
        print(f"Login error: {e}")
        return False

    print(f"Login successful for uploading {video_path}.")

    # Step 2: Generate thumbnail
    with tempfile.TemporaryDirectory() as temp_dir:
        thumbnail_path = generate_thumbnail(video_path, temp_dir)
        if not thumbnail_path:
            print(f"Skipping upload for {video_path} due to thumbnail generation failure.")
            return False

        # Step 3: Prepare upload data
        title = os.path.basename(video_path).rsplit('.', 1)[0]  # Filename without extension
        description = 'please enjoy(≧▽≦)'  # Empty as per requirement

        # Step 4: Upload with explicit file handle management
        video_file = None
        thumbnail_file = None
        try:
            video_file = open(video_path, 'rb')
            thumbnail_file = open(thumbnail_path, 'rb')
            thumbnail_name = os.path.basename(thumbnail_path)
            files = {
                'video': (os.path.basename(video_path), video_file, 'video/mp4'),
                'thumbnail': (thumbnail_name, thumbnail_file, 'image/jpeg')
            }
            data = {
                'title': title,
                'description': description
            }
            upload_response = session.post(upload_url, files=files, data=data)
            if upload_response.status_code == 200:
                print(f"Successfully uploaded {video_path}.")
                return True
            else:
                print(f"Upload failed for {video_path} with status code: {upload_response.status_code}")
                print(upload_response.text)
                return False
        except (requests.RequestException, FileNotFoundError) as e:
            print(f"Upload error for {video_path}: {e}")
            return False
        finally:
            # Explicitly close file handles
            if video_file:
                video_file.close()
            if thumbnail_file:
                thumbnail_file.close()

def batch_upload_videos(login_url, upload_url, username, password, directory='./'):
    """
    Batch upload all video files in the specified directory.
    Supports common video formats (.mp4, .avi, .mkv, .mov).
    """
    video_extensions = ('.mp4', '.avi', '.mkv', '.mov')
    video_files = [
        os.path.join(directory, f) for f in os.listdir(directory)
        if os.path.isfile(os.path.join(directory, f)) and f.lower().endswith(video_extensions)
    ]
    
    if not video_files:
        print(f"No video files found in directory: {directory}")
        return

    print(f"Found {len(video_files)} video files to upload.")
    success_count = 0
    for video_path in video_files:
        print(f"Processing {video_path}...")
        if upload_video(login_url, upload_url, username, password, video_path):
            success_count += 1

    print(f"Batch upload complete: {success_count}/{len(video_files)} videos uploaded successfully.")

if __name__ == "__main__":
    login_url = 'http://vhost.locherli.my/login.php'  # Replace with your login URL
    upload_url = 'http://vhost.locherli.my/upload.php'  # Replace with your upload URL
    username = 'locher'  # Replace with your username
    password = 'locher@123'  # Replace with your password
    directory = './more'  # Directory containing videos
    batch_upload_videos(login_url, upload_url, username, password, directory)