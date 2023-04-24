## php-tools

#### 项目介绍
> php开发辅助类以及函数

- 数组帮助类
- 文件帮助类
- 树形结构帮助类

#### 安装前准备
1. 安装git
2. 安装docker
3. 安装docker-compose

#### 安装步骤

1. 下载代码

    ```
    git clone git@github.com:fuliang10000/docker.git
    ```

2. 切换到指定分子

    ```
    cd docker
    git checkout -b wnmp origin/wnmp
    ```

2. 构建并启动服务

    ```
    docker-compose build
    docker-compose up -d
    ```
3. 查看服务启动情况

    ```
    docker-compose ps
    ```

4. 停止服务

    ```
    docker-compose down
    ```
   