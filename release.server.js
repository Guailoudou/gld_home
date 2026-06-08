import Client from 'ssh2-sftp-client';
import chalk from 'chalk';
import ora from 'ora';
import fs from 'fs'; 
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
// 1. 获取命令行传入的环境参数 (例如: node release.server.js prod)
const privateKeyPath = path.resolve(__dirname, '.ssh/id_rsa'); 
const ipPath = path.resolve(__dirname, '.ssh/ip');
const ip = fs.readFileSync(ipPath, 'utf8').trim();

// 2. 配置不同环境的服务器信息
const serverConfig = {
    
    prod: {
        host: ip,   
        port: 22,
        username: 'root',
        privateKey: fs.readFileSync(privateKeyPath), 
        localPath: './dist',
        remotePath: '/www/wwwroot/www.gldhn.top',
        rmPath: '/www/wwwroot/www.gldhn.top/assets'
    }
};

async function deploy() {
    const config = serverConfig.prod;
    
    if (!config) {
        console.log(chalk.red(`❌ 错误: 未找到 [${env}] 环境的配置！`));
        return;
    }

    const sftp = new Client();
    const spinner = ora(`🚀 正在发布到 ${chalk.yellow('生产环境')}...`).start();

    try {
        // 3. 连接服务器
        await sftp.connect(config);
        spinner.text = '✅ 服务器连接成功，开始检查远程目录...';

        // 4. 确保远程目录存在
        // 检查并创建远程目录
        const exists = await sftp.exists(config.remotePath);
        if (!exists) {
            await sftp.mkdir(config.remotePath, true); 
        }

        // 检查并删除旧版本文件
        spinner.text = '🧹 正在删除旧版本文件...';
        const rmExists = await sftp.exists(config.rmPath);
        if (rmExists) {
            await sftp.rmdir(config.rmPath, true); 
        }


        // 6. 递归上传新文件
        spinner.text = '📤 正在上传最新构建文件...';
        await sftp.uploadDir(config.localPath, config.remotePath);

        spinner.succeed(chalk.green(`🎉 生产环境部署成功！`));
    } catch (err) {
        spinner.fail(chalk.red('❌ 部署失败'));
        console.error(chalk.red(err.message));
    } finally {
        // 7. 无论成功与否，关闭连接
        await sftp.end();
    }
}

deploy();