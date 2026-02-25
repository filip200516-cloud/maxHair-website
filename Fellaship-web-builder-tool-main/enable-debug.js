const SFTPClient = require('ssh2-sftp-client');
const sftp = new SFTPClient();

async function enableDebug() {
  try {
    await sftp.connect({
      host: '92.113.19.58',
      port: 65002,
      username: 'u946900008',
      password: '123SSH_Fellaship'
    });
    const wpConfig = '/home/u946900008/domains/maxhair.cz/public_html/wp-config.php';
    const data = await sftp.get(wpConfig);
    let content = data.toString();
    
    // Enable WP_DEBUG
    content = content.replace(
      /define\s*\(\s*['"]WP_DEBUG['"]\s*,\s*false\s*\)/,
      "define('WP_DEBUG', true)"
    );
    
    // Add WP_DEBUG_LOG if not present
    if (!content.includes('WP_DEBUG_LOG')) {
      content = content.replace(
        "define('WP_DEBUG', true)",
        "define('WP_DEBUG', true);\ndefine('WP_DEBUG_LOG', true);"
      );
    }
    
    await sftp.put(Buffer.from(content), wpConfig);
    console.log('WP_DEBUG enabled');
    await sftp.end();
  } catch (err) {
    console.error('Error:', err.message);
  }
}

enableDebug();
