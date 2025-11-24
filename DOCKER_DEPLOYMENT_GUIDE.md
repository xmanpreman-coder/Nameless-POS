# 🐳 Docker Deployment Guide - Nameless POS

## 📋 Overview

Docker memberikan solusi deployment yang:
- ✅ **Konsisten** di semua environment
- ✅ **Scalable** dari single PC sampai cluster
- ✅ **Isolated** - tidak bentrok dengan software lain
- ✅ **Easy backup** - cukup copy volumes
- ✅ **Zero downtime updates** dengan rolling deployment

## 🎯 Deployment Options

### 1. SQLite Single Container (Recommended for Warnet)

**Best for:** Single cashier, small business, testing

```powershell
.\docker-deploy.ps1 -Mode sqlite
```

**Features:**
- ✅ All-in-one container
- ✅ SQLite database (no MySQL setup)
- ✅ Fast performance
- ✅ Easy backup & restore
- ✅ Perfect untuk warnet/toko kecil

**Access:** `http://localhost:8000`

### 2. MySQL Multi-Container (Scalable)

**Best for:** Multiple cashiers, growing business

```powershell
.\docker-deploy.ps1 -Mode mysql
```

**Features:**
- ✅ Web + Database terpisah
- ✅ Persistent database storage
- ✅ Multiple user support
- ✅ Professional database management

**Access:** 
- App: `http://localhost:8000`
- MySQL: `localhost:3306`

### 3. Production Setup (Enterprise)

**Best for:** Production server, cloud deployment

```powershell
.\docker-deploy.ps1 -Mode production
```

**Features:**
- ✅ Nginx reverse proxy
- ✅ Redis caching
- ✅ SSL/HTTPS support
- ✅ Health checks
- ✅ Auto-restart on failure

## 🚀 Quick Start

### Prerequisites
1. Install Docker Desktop
2. Ensure Docker is running
3. Have .env file ready

### Deploy Commands

```powershell
# Single container SQLite (easiest)
.\docker-deploy.ps1 -Mode sqlite

# Multi-container MySQL (scalable)  
.\docker-deploy.ps1 -Mode mysql

# Production setup (enterprise)
.\docker-deploy.ps1 -Mode production
```

## 🔧 Management Commands

### Daily Operations
```powershell
# View logs
docker-compose logs -f

# Restart services
docker-compose restart

# Stop all containers
docker-compose down

# Update and restart
docker-compose down
docker-compose up -d --build
```

### Backup & Restore
```powershell
# Backup SQLite
copy database\database.sqlite backup\

# Backup MySQL
docker-compose exec db mysqldump -u root -p nameless_pos > backup.sql

# Restore MySQL
docker-compose exec -T db mysql -u root -p nameless_pos < backup.sql
```

### Monitoring
```powershell
# Container status
docker ps

# Resource usage
docker stats

# Container shell access
docker-compose exec web bash
```

## 🌐 Network Deployment Scenarios

### Scenario 1: Single Computer Setup
```
Computer: Docker + SQLite
Users: Direct access via browser
URL: http://localhost:8000
```

### Scenario 2: Local Network (Warnet)
```
Server: Docker container
Clients: Browser access via IP
URL: http://192.168.1.100:8000
```

Setup:
```powershell
# Run on server computer
.\docker-deploy.ps1 -Mode mysql

# Clients access via server IP
# http://192.168.1.100:8000
```

### Scenario 3: Cloud Deployment
```
Server: VPS/Cloud with Docker
Clients: Internet access via domain
URL: https://yourpos.com
```

## 🔄 Update Strategy

### Zero-Downtime Updates
```powershell
# 1. Pull latest code
git pull origin main

# 2. Build new image
docker build -t nameless-pos:new .

# 3. Rolling update
docker-compose up -d --no-deps web

# 4. Cleanup old images
docker image prune -f
```

### Backup Before Update
```powershell
# 1. Backup database
.\docker-deploy.ps1 -Mode backup

# 2. Update application
git pull && docker-compose up -d --build

# 3. Verify functionality
```

## 📊 Performance Optimization

### Resource Limits
```yaml
# Add to docker-compose.yaml
services:
  web:
    deploy:
      resources:
        limits:
          cpus: '1.0'
          memory: 512M
        reservations:
          memory: 256M
```

### Database Optimization
```yaml
# MySQL optimizations
db:
  environment:
    - MYSQL_INNODB_BUFFER_POOL_SIZE=256M
    - MYSQL_QUERY_CACHE_SIZE=64M
```

### Nginx Caching
```nginx
# Add to nginx.conf
location ~* \.(css|js|jpg|png|gif|ico|svg)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## 🔒 Security Best Practices

### Production Environment
```yaml
# Environment variables
- APP_ENV=production
- APP_DEBUG=false
- DB_PASSWORD=strong_random_password
```

### Firewall Rules
```powershell
# Allow only necessary ports
# 80, 443 for web access
# 22 for SSH (if needed)
# Block direct database access (3306)
```

### SSL/HTTPS Setup
```yaml
# Use Let's Encrypt + Nginx
nginx:
  volumes:
    - ./ssl:/etc/ssl
    - ./nginx-ssl.conf:/etc/nginx/nginx.conf
```

## 📈 Scaling Options

### Horizontal Scaling
```yaml
# Scale web servers
services:
  web:
    deploy:
      replicas: 3
```

### Load Balancer
```yaml
# Add HAProxy or Nginx load balancer
load_balancer:
  image: haproxy:alpine
  depends_on:
    - web
```

## 🔍 Troubleshooting

### Common Issues

**Container won't start:**
```powershell
# Check logs
docker-compose logs web

# Check port conflicts
netstat -an | findstr :8000
```

**Database connection failed:**
```powershell
# Wait for MySQL to be ready
docker-compose exec db mysql -u root -p -e "SHOW DATABASES;"
```

**Permission errors:**
```powershell
# Fix file permissions
docker-compose exec web chown -R www-data:www-data storage
```

## 🎯 Comparison: Docker vs Other Options

| Feature | Docker | Electron | Traditional |
|---------|--------|----------|-------------|
| **Deployment** | Very Easy | Medium | Complex |
| **Updates** | Zero downtime | Auto-update | Manual |
| **Scalability** | Excellent | Limited | Manual |
| **Resource Usage** | Medium | High | Low |
| **Isolation** | Perfect | Good | None |
| **Printer Access** | Network | Direct | Direct |
| **Offline Mode** | Partial | Full | Full |

## 💡 Recommendations

### For Warnet/Small Business:
```
✅ Use: SQLite single container
✅ Deploy: Local computer with Docker
✅ Access: http://localhost:8000
✅ Backup: Copy database file
```

### For Growing Business:
```
✅ Use: MySQL multi-container
✅ Deploy: Dedicated server
✅ Access: http://server-ip:8000
✅ Backup: MySQL dumps
```

### For Enterprise:
```
✅ Use: Production setup with load balancer
✅ Deploy: Cloud VPS/AWS/Google Cloud
✅ Access: https://yourdomain.com
✅ Backup: Automated daily backups
```

---

🚀 **Quick Start Command:**
```powershell
.\docker-deploy.ps1 -Mode sqlite
```

📞 **Need Help?** Check logs dengan: `docker-compose logs -f`