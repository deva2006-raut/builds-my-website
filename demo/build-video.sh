#!/usr/bin/env bash
# Builds demo/demo-walkthrough.mp4 from the raw screen capture plus title cards.
# Usage: bash demo/build-video.sh [raw-capture.mp4]
set -euo pipefail

FF="../tools/ffmpeg/ffmpeg-9.1-essentials_build/bin/ffmpeg.exe"
FONT=demo/segoeui.ttf
cp -f "C:/Windows/Fonts/segoeui.ttf" demo/segoeui.ttf
RAW="${1:-demo/demo-walkthrough.mp4}"
BODY_SECONDS=192

cd "$(dirname "$0")/.."

cat > demo/intro.txt <<'TXT'
AI Builds My Website
Laravel 12  ·  MySQL  ·  Gemini API

Silent screen walkthrough — no voice-over.
Devanshu Raut  ·  github.com/deva2006-raut/builds-my-website
TXT

cat > demo/outro.txt <<'TXT'
Repo: github.com/deva2006-raut/builds-my-website
README.md  ·  DECISIONS.md  ·  .env.example (no secrets)

Expected stipend: as per company policy
Earliest joining date: immediately

Recorded on the real app: sources read, model wrote the site,
one section edited by hand, published, public page opened.
TXT

card() { # $1=textfile $2=seconds $3=out
  "$FF" -hide_banner -loglevel error -y -f lavfi -i "color=c=0x0b1020:s=1280x720:d=$2" -r 25 \
    -vf "drawtext=fontfile=$FONT:textfile=$1:fontcolor=white:fontsize=34:line_spacing=16:x=(w-text_w)/2:y=(h-text_h)/2,drawbox=x=0:y=0:w=1280:h=8:color=0x6366f1:t=fill" \
    -c:v libx264 -pix_fmt yuv420p -preset veryfast -crf 22 "$3"
  echo "card -> $3"
}

card demo/intro.txt 7 demo/seg-intro.mp4
card demo/outro.txt 11 demo/seg-outro.mp4

"$FF" -hide_banner -loglevel error -y -i "$RAW" -t $BODY_SECONDS -r 25 \
  -c:v libx264 -pix_fmt yuv420p -preset veryfast -crf 24 demo/seg-body.mp4
echo "body -> demo/seg-body.mp4"

printf "file 'seg-intro.mp4'\nfile 'seg-body.mp4'\nfile 'seg-outro.mp4'\n" > demo/concat.txt
"$FF" -hide_banner -loglevel error -y -f concat -safe 0 -i demo/concat.txt \
  -c:v libx264 -pix_fmt yuv420p -preset veryfast -crf 23 -movflags +faststart demo/website-builder-demo.mp4

ls -la demo/website-builder-demo.mp4
"$FF" -hide_banner -i demo/website-builder-demo.mp4 2>&1 | grep -E "Duration|Stream"
