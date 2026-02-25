# Video-to-Cursor Setup Guide (Windows)

This guide helps you convert video content into formats that Cursor's vision models (GPT-4o) can analyze—useful for UI bugs, screen recordings, and workflow analysis.

---

## Quick Start

| Method | Best For | Setup Time |
|--------|----------|------------|
| **1. llm-video-frames** | Full video analysis with GPT-4o | ~5 min |
| **2. ffmpeg (manual)** | Quick frame extraction, drag into Cursor | ~2 min |
| **3. Whisper** | Audio/transcript from video | ~5 min |
| **4. video-analyzer** | Local processing, no API | ~10 min |

---

## Prerequisites

### 1. Install FFmpeg (Required for methods 1 & 2)

**Option A: winget (recommended)**
```powershell
winget install FFmpeg
```
Then restart your terminal.

**Option B: Chocolatey**
```powershell
choco install ffmpeg
```

**Option C: Manual**
1. Download from https://ffmpeg.org/download.html (Windows builds)
2. Extract and add the `bin` folder to your PATH

**Verify:** `ffmpeg -version`

---

## Method 1: llm-video-frames (Best for GPT-4o)

Turns video into a sequence of JPEG frames that vision models analyze in order.

### Installation

```powershell
pip install llm llm-video-frames
llm keys set openai
```
(Enter your OpenAI API key when prompted—Cursor may use its own key if configured)

### Usage

```powershell
# Basic: 1 frame per second
llm -f video-frames:video.mp4 "describe the key UI elements in this video" -m gpt-4o

# More frames (2 per second)
llm -f "video-frames:video.mp4?fps=2" "analyze this screen recording" -m gpt-4o

# With timestamps on each frame
llm -f "video-frames:video.mp4?timestamps=1" "what happens at each step?" -m gpt-4o
```

### Cursor Workflow

1. Run the command above to get a text description
2. Or: extract frames with the script below, then **drag the JPEGs into Cursor Chat** and prompt: *"Here is a screen recording of a bug. Analyze the frames and generate the corresponding React code."*

---

## Method 2: device-to-cursor (Recommended for Website Dev)

**Extracts 3 fps by default** – optimized for feeding device recordings to Cursor.

```powershell
cd video-to-cursor
.\device-to-cursor.ps1 "path\to\your\device-recording.mp4"
```

Outputs to `device-frames/` in your project. In Cursor: type `@device-frames` or drag the folder into Chat.

---

## Method 2b: FFmpeg Manual Frame Extraction

No Python/LLM tools needed. Extract frames and drag them into Cursor.

### Using the Script

```powershell
.\extract-frames.ps1 -InputVideo "path\to\video.mp4" -OutputDir "frames" -Fps 1
```

### Manual Command

```powershell
# 1 frame per second
ffmpeg -i input_video.mp4 -vf fps=1 frame_%04d.jpg

# 1 frame every 2 seconds
ffmpeg -i input_video.mp4 -vf "fps=1/2" frame_%04d.jpg
```

**Workflow:** Run the script, then drag the resulting images into Cursor Composer/Chat and ask: *"Based on these frames, recreate this layout."*

---

## Method 3: Video-to-Text (Audio/Transcript)

For videos where the content is in dialogue (meetings, tutorials).

### Install whisper-ctranslate2

```powershell
pip install whisper-ctranslate2
```

### Usage

```powershell
whisper-ctranslate2 video.mp4 --output_format txt
```

**Workflow:** Paste the `.txt` output into Cursor and ask: *"Based on this meeting transcript, write a task list."*

---

## Method 4: video-analyzer (Local)

Python tool using OpenCV + Whisper + optional Ollama for local analysis.

```powershell
pip install video-analyzer
video-analyzer video.mp4
```

Provides structured JSON/text. Feed the output to Cursor for further processing.

---

## Recommended Cursor Workflow

1. **UI bugs / screen recordings:** Use `extract-frames.ps1` → drag frames into Cursor → *"Analyze these frames and fix the bug"*
2. **Full video analysis:** Use `llm -f video-frames:video.mp4 "..."` → copy response into Cursor
3. **Meeting notes:** Use Whisper → paste transcript → *"Create a task list from this transcript"*

---

## Files in This Folder

| File | Purpose |
|------|---------|
| `README.md` | This guide |
| `device-to-cursor.ps1` | **Device recordings → 3 fps frames** for Cursor (recommended) |
| `extract-frames.ps1` | General FFmpeg frame extraction (configurable fps) |
| `requirements-video.txt` | Python deps for llm-video-frames, whisper |
