#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   ./scripts/make_smooth_loops.sh
#   ./scripts/make_smooth_loops.sh 0.8
# Produces:
#   loop_smooth_vidbg.mp4
#   loop_smooth_robotwhole.mp4

OVERLAP="${1:-0.8}"
INPUTS=("vidbg.mp4" "robotwhole.MP4")

if ! command -v ffmpeg >/dev/null 2>&1; then
    echo "ffmpeg is required but not found in PATH."
    exit 1
fi

if ! command -v ffprobe >/dev/null 2>&1; then
    echo "ffprobe is required but not found in PATH."
    exit 1
fi

if ! awk "BEGIN { exit !($OVERLAP > 0.39 && $OVERLAP < 2.01) }"; then
    echo "Overlap must be between 0.4 and 2.0 seconds."
    exit 1
fi

for input in "${INPUTS[@]}"; do
    if [[ ! -f "$input" ]]; then
        echo "Skipping missing input: $input"
        continue
    fi

    duration="$(ffprobe -v error -show_entries format=duration -of default=nokey=1:noprint_wrappers=1 "$input")"
    if ! awk "BEGIN { exit !($duration > $OVERLAP) }"; then
        echo "Skipping $input: duration ($duration) must be greater than overlap ($OVERLAP)."
        continue
    fi
    offset="$(awk "BEGIN { printf \"%.6f\", $duration - $OVERLAP }")"

    base="${input##*/}"
    base="${base%.*}"
    output="loop_smooth_${base}.mp4"

    has_audio="$(ffprobe -v error -select_streams a -show_entries stream=index -of csv=p=0 "$input" | head -n 1 || true)"

    if [[ -n "$has_audio" ]]; then
        ffmpeg -y -i "$input" \
            -filter_complex "[0:v]split=2[v_main][v_head];[v_main]setpts=PTS-STARTPTS[v_main_pts];[v_head]trim=0:${OVERLAP},setpts=PTS-STARTPTS[v_head_pts];[v_main_pts][v_head_pts]xfade=transition=fade:duration=${OVERLAP}:offset=${offset},format=yuv420p[vout];[0:a]asplit=2[a_main][a_head];[a_main]asetpts=PTS-STARTPTS[a_main_pts];[a_head]atrim=0:${OVERLAP},asetpts=PTS-STARTPTS[a_head_pts];[a_main_pts][a_head_pts]acrossfade=d=${OVERLAP}:c1=tri:c2=tri[aout]" \
            -map "[vout]" -map "[aout]" \
            -c:v libx264 -crf 18 -preset medium \
            -c:a aac -b:a 192k \
            -movflags +faststart \
            "$output"
    else
        ffmpeg -y -i "$input" \
            -filter_complex "[0:v]split=2[v_main][v_head];[v_main]setpts=PTS-STARTPTS[v_main_pts];[v_head]trim=0:${OVERLAP},setpts=PTS-STARTPTS[v_head_pts];[v_main_pts][v_head_pts]xfade=transition=fade:duration=${OVERLAP}:offset=${offset},format=yuv420p[vout]" \
            -map "[vout]" \
            -c:v libx264 -crf 18 -preset medium \
            -movflags +faststart \
            "$output"
    fi

    echo "Created: $output"
done
