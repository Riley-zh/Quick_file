# Quick_file 文件管理系统

## 项目简介
Quick_file 是一个轻量级的文件管理工具，旨在提供快速、便捷的文件上传、下载、删除和管理功能。

## 项目结构
```
Quick_file/
├── public/                 # 公共访问目录
│   ├── index.html          # 前端主页面
│   ├── index.php           # API入口文件
│   └── favicon.ico         # 网站图标
├── src/                    # 源代码目录
│   ├── config.php          # 配置文件
│   ├── Controller/         # 控制器目录
│   │   ├── upload.php      # 文件上传处理
│   │   ├── download.php    # 文件下载处理
│   │   ├── delete.php      # 文件删除处理
│   │   └── list.php        # 文件列表处理
│   ├── Service/            # 服务层目录
│   │   ├── file_manager.php# 文件管理服务
│   │   └── encryption.php  # 加密服务
│   └── Model/              # 模型目录（预留）
├── storage/                # 存储目录
│   ├── data/               # 文件存储目录
│   └── chunks/             # 文件分片存储目录（预留）
├── LICENSE                 # 开源许可证
└── README.md               # 项目说明文档
```

## 功能特性
- 文件上传
- 文件下载
- 文件列表展示
- 文件删除
- 文件加密存储

## 环境要求
- PHP 7.x 或以上版本
- 启用 OpenSSL 扩展
- Web服务器（Apache/Nginx）

## 安装部署
1. 将项目文件放置在Web服务器根目录
2. 确保 `storage/data` 和 `storage/chunks` 目录有写入权限
3. 启动Web服务器
4. 访问 `http://your-domain/index.html` 使用文件管理系统

## API接口
- `POST /index.php?path=upload` - 上传文件
- `GET /index.php?path=list` - 获取文件列表
- `GET /index.php?path=download&id={fileId}` - 下载文件
- `POST /index.php?path=delete` - 删除文件

## 安全说明
- 所有文件元数据均经过AES-256加密存储
- 支持文件类型和大小限制
- 上传文件名经过安全过滤