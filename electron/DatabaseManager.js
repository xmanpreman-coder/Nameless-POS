const fs = require('fs');
const path = require('path');
const { dialog } = require('electron');
const os = require('os');

class DatabaseManager {
  constructor() {
    this.databasePath = path.join(__dirname, '..', 'database', 'database.sqlite');
  }

  /**
   * Backup database ke file
   * @returns {Promise<object>} {success: boolean, message: string, filePath: string}
   */
  async backupDatabase() {
    try {
      console.log('[DatabaseManager] Starting backup...');
      
      // Check if database exists
      if (!fs.existsSync(this.databasePath)) {
        console.log('[DatabaseManager] Database not found at:', this.databasePath);
        return {
          success: false,
          message: 'Database file not found'
        };
      }

      // Create backup directory in user AppData
      const backupDir = path.join(
        os.homedir(),
        'AppData',
        'Roaming',
        'Nameless POS',
        'backups'
      );

      if (!fs.existsSync(backupDir)) {
        fs.mkdirSync(backupDir, { recursive: true });
        console.log('[DatabaseManager] Created backup directory:', backupDir);
      }

      // Generate backup filename with timestamp
      const timestamp = new Date().toISOString()
        .replace(/[:.]/g, '-')
        .split('T')[0] + '_' + Date.now();
      const backupFileName = `database_${timestamp}.sqlite`;
      const backupFilePath = path.join(backupDir, backupFileName);

      // Copy database file
      fs.copyFileSync(this.databasePath, backupFilePath);
      console.log('[DatabaseManager] Backup created:', backupFilePath);

      return {
        success: true,
        message: `Backup created: ${backupFileName}`,
        filePath: backupFilePath,
        fileName: backupFileName
      };

    } catch (error) {
      console.error('[DatabaseManager] Backup error:', error);
      return {
        success: false,
        message: `Backup error: ${error.message}`
      };
    }
  }

  /**
   * List all backups
   * @returns {Promise<array>} Array of backup file info
   */
  async listBackups() {
    try {
      const backupDir = path.join(
        os.homedir(),
        'AppData',
        'Roaming',
        'Nameless POS',
        'backups'
      );

      if (!fs.existsSync(backupDir)) {
        return [];
      }

      const files = fs.readdirSync(backupDir);
      const backups = files
        .filter(f => f.endsWith('.sqlite'))
        .map(f => {
          const filePath = path.join(backupDir, f);
          const stats = fs.statSync(filePath);
          return {
            name: f,
            path: filePath,
            size: (stats.size / 1024 / 1024).toFixed(2) + ' MB',
            created: stats.ctime,
            createdFormatted: new Date(stats.ctime).toLocaleString()
          };
        })
        .sort((a, b) => b.created - a.created);

      return backups;

    } catch (error) {
      console.error('[DatabaseManager] List backups error:', error);
      return [];
    }
  }

  /**
   * Restore database from backup
   * @param {string} backupFilePath - Full path to backup file
   * @returns {Promise<object>} {success: boolean, message: string}
   */
  async restoreDatabase(backupFilePath) {
    try {
      console.log('[DatabaseManager] Starting restore from:', backupFilePath);

      // Validate backup file exists
      if (!fs.existsSync(backupFilePath)) {
        return {
          success: false,
          message: 'Backup file not found'
        };
      }

      // Create backup of current database first
      if (fs.existsSync(this.databasePath)) {
        const currentBackupPath = this.databasePath + '.pre_restore_' + Date.now();
        fs.copyFileSync(this.databasePath, currentBackupPath);
        console.log('[DatabaseManager] Created pre-restore backup:', currentBackupPath);
      }

      // Restore database
      fs.copyFileSync(backupFilePath, this.databasePath);
      console.log('[DatabaseManager] Database restored successfully');

      return {
        success: true,
        message: 'Database restored successfully. Please restart the application.'
      };

    } catch (error) {
      console.error('[DatabaseManager] Restore error:', error);
      return {
        success: false,
        message: `Restore error: ${error.message}`
      };
    }
  }

  /**
   * Delete a backup file
   * @param {string} backupFilePath - Full path to backup file
   * @returns {Promise<object>} {success: boolean, message: string}
   */
  async deleteBackup(backupFilePath) {
    try {
      if (!fs.existsSync(backupFilePath)) {
        return {
          success: false,
          message: 'Backup file not found'
        };
      }

      fs.unlinkSync(backupFilePath);
      console.log('[DatabaseManager] Backup deleted:', backupFilePath);

      return {
        success: true,
        message: 'Backup deleted successfully'
      };

    } catch (error) {
      console.error('[DatabaseManager] Delete backup error:', error);
      return {
        success: false,
        message: `Delete error: ${error.message}`
      };
    }
  }

  /**
   * Get database info
   * @returns {object} Database file information
   */
  getDatabaseInfo() {
    try {
      if (fs.existsSync(this.databasePath)) {
        const stats = fs.statSync(this.databasePath);
        return {
          path: this.databasePath,
          size: (stats.size / 1024 / 1024).toFixed(2) + ' MB',
          created: stats.ctime,
          modified: stats.mtime,
          modifiedFormatted: new Date(stats.mtime).toLocaleString()
        };
      }
      return null;
    } catch (error) {
      console.error('[DatabaseManager] Get info error:', error);
      return null;
    }
  }
}

module.exports = DatabaseManager;
