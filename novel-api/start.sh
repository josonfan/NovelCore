#!/bin/bash

# 检查进程是否正在运行
function is_process_running {
    local pid_file=$1
    if [ -f "$pid_file" ]; then
        local pid=$(cat "$pid_file")
        if ps -p "$pid" > /dev/null; then
            return 0  # 进程正在运行
        else
            rm "$pid_file"  # 清理无效的 PID 文件
        fi
    fi
    return 1  # 进程未运行
}

# 封装启动异步任务函数
function start_async_task {
    local queue_name=$1
    local log_prefix=$2
    local pid_file=$3
    if is_process_running "$pid_file"; then
        echo "${queue_name} is already running"
    else
        nohup php84 think queue:listen --queue "$queue_name" --sleep 10 --timeout 3600 >> "runtime/${log_prefix}_$(date +%Y-%m-%d).log" 2>&1 &
        echo "$!" > "$pid_file"
        echo "${queue_name} start success"
    fi
}

# 启动异步任务
for i in $(seq 1 3);
do
    start_async_task "bl_exec_method_custom${i}" "bl_async${i}" "bl_async${i}_pid"
done
# 启动同步异步任务
for i in $(seq 1 2);
do
    start_async_task "bl_sync_exec_method_custom${i}" "bl_sync_async${i}" "bl_sync_async${i}_pid"
done
