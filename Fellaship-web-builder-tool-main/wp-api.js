import axios from 'axios';
import FormData from 'form-data';
import fs from 'fs-extra';
import path from 'path';

/**
 * WordPress REST API klient
 */
export class WordPressAPI {
  constructor(config) {
    this.baseURL = config.wordpress.url;
    this.username = config.wordpress.username;
    // Preferovat Application Password (doporučeno v dokumentaci)
    // Application Password může mít mezery - odstranit je pro Basic Auth
    const appPassword = config.wordpress.applicationPassword?.replace(/\s+/g, '') || '';
    this.password = appPassword || config.wordpress.password;
    this.apiURL = `${this.baseURL}/wp-json/wp/v2`;
    
    // Vytvoření axios instance s autentizací
    this.client = axios.create({
      baseURL: this.apiURL,
      auth: {
        username: this.username,
        password: this.password
      },
      headers: {
        'Content-Type': 'application/json'
      }
    });
  }

  /**
   * Test připojení k API
   */
  async testConnection() {
    try {
      const response = await this.client.get('/');
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Získat všechny stránky
   */
  async getPages() {
    try {
      // Zkusit nejdřív s status: 'any'
      const response = await this.client.get('/pages', {
        params: {
          per_page: 100,
          status: 'any'
        }
      });
      return { success: true, data: response.data };
    } catch (error) {
      // Pokud selže, zkusit bez status parametru (pouze publikované)
      try {
        const response = await this.client.get('/pages', {
          params: {
            per_page: 100
          }
        });
        return { success: true, data: response.data };
      } catch (error2) {
        return { 
          success: false, 
          error: error.message,
          details: error.response?.data || error2.response?.data
        };
      }
    }
  }

  /**
   * Získat všechny stránky bez status parametru
   */
  async getPagesWithoutStatus() {
    try {
      const response = await this.client.get('/pages', {
        params: {
          per_page: 100
        }
      });
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Získat stránku podle slug
   */
  async getPageBySlug(slug) {
    try {
      const response = await this.client.get('/pages', {
        params: {
          slug: slug,
          status: 'any'
        }
      });
      
      if (response.data && response.data.length > 0) {
        return { success: true, data: response.data[0] };
      }
      return { success: false, error: 'Page not found' };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Získat metadata stránky (včetně Bricks obsahu)
   */
  async getPageMeta(pageId) {
    try {
      const response = await this.client.get(`/pages/${pageId}`, {
        params: {
          context: 'edit'
        }
      });
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Aktualizovat stránku
   */
  async updatePage(pageId, data) {
    try {
      const response = await this.client.post(`/pages/${pageId}`, data);
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Vytvořit stránku
   */
  async createPage(data) {
    try {
      const response = await this.client.post('/pages', data);
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Získat Bricks obsah stránky z meta
   */
  async getBricksContent(pageId, metaKey = '_bricks_page_content') {
    try {
      // Nejdřív zkusíme custom Bricks endpoint (pokud je plugin nainstalován)
      try {
        const customResponse = await this.client.get(`/bricks/v1/page/${pageId}/content`, {
          baseURL: this.baseURL + '/wp-json'
        });
        
        if (customResponse.data) {
          return { 
            success: true, 
            data: customResponse.data 
          };
        }
      } catch (customError) {
        // Custom endpoint není dostupný, použijeme alternativní metody
        console.log('Custom Bricks endpoint not available, trying alternative methods...');
      }

      // Alternativní metoda 1: Zkusit standardní meta endpoint
      try {
        const metaResult = await this.client.get(`/pages/${pageId}/meta`);
        const meta = metaResult.data || {};
        
        if (meta[metaKey]) {
          return { 
            success: true, 
            data: typeof meta[metaKey] === 'string' ? JSON.parse(meta[metaKey]) : meta[metaKey]
          };
        }
      } catch (metaError) {
        // Meta endpoint není dostupný
      }

      // Alternativní metoda 2: Zkusit získat přes edit kontext
      try {
        const pageResult = await this.getPageMeta(pageId);
        if (pageResult.success && pageResult.data.meta && pageResult.data.meta[metaKey]) {
          const content = pageResult.data.meta[metaKey];
          return { 
            success: true, 
            data: typeof content === 'string' ? JSON.parse(content) : content
          };
        }
      } catch (pageError) {
        // Page meta není dostupné
      }

      return { 
        success: false, 
        error: 'Bricks content not found. Install Bricks API Endpoint plugin for full functionality.',
        hint: 'Install the bricks-api-endpoint.php plugin to enable Bricks content sync.'
      };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Aktualizovat Bricks obsah stránky
   */
  async updateBricksContent(pageId, bricksData, metaKey = '_bricks_page_content') {
    try {
      // Nejdřív zkusíme custom Bricks endpoint (pokud je plugin nainstalován)
      try {
        const contentString = typeof bricksData === 'string' 
          ? bricksData 
          : JSON.stringify(bricksData);
        
        const customResponse = await this.client.post(
          `/bricks/v1/page/${pageId}/content`,
          { content: contentString },
          {
            baseURL: this.baseURL + '/wp-json'
          }
        );
        
        if (customResponse.data && customResponse.data.success) {
          return { 
            success: true, 
            data: customResponse.data.content || customResponse.data
          };
        }
      } catch (customError) {
        // Custom endpoint není dostupný, použijeme alternativní metody
        // Alternativní metoda: Aktualizovat stránku přímo s meta daty
        try {
          // Dekódovat obsah, pokud je to string
          let contentToSave = typeof bricksData === 'string' ? JSON.parse(bricksData) : bricksData;
          
          // Aktualizovat stránku s meta daty
          const response = await this.client.post(`/pages/${pageId}`, {
            meta: {
              [metaKey]: contentToSave,
              '_bricks_page_content_2': contentToSave,
              '_bricks_editor_mode': 'bricks',
              '_bricks_page_content_type': 'bricks'
            }
          });
          
          return { success: true, data: response.data };
        } catch (updateError) {
          return { 
            success: false, 
            error: 'Cannot update Bricks content. Install Bricks API Endpoint plugin for full functionality.',
            hint: 'Install the bricks-api-endpoint.php plugin to enable Bricks content sync.',
            details: customError?.response?.data || updateError?.response?.data
          };
        }
      }
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Zkontrolovat, zda je Bricks nainstalován
   */
  async isBricksInstalled() {
    try {
      const response = await this.client.get('/plugins', {
        params: {
          search: 'bricks'
        }
      });
      
      const bricksPlugin = response.data?.find(
        plugin => plugin.plugin?.includes('bricks') || plugin.name?.toLowerCase().includes('bricks')
      );
      
      return { 
        success: true, 
        installed: !!bricksPlugin,
        plugin: bricksPlugin 
      };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Nainstalovat plugin ze ZIP souboru
   */
  async installPlugin(zipFilePath) {
    try {
      const formData = new FormData();
      formData.append('plugin', fs.createReadStream(zipFilePath));
      
      const response = await axios.post(
        `${this.baseURL}/wp-admin/admin-ajax.php?action=upload-plugin`,
        formData,
        {
          auth: {
            username: this.username,
            password: this.password
          },
          headers: {
            ...formData.getHeaders()
          },
          maxContentLength: Infinity,
          maxBodyLength: Infinity
        }
      );

      return { success: true, data: response.data };
    } catch (error) {
      // Alternativní metoda: použít custom endpoint
      try {
        const formData2 = new FormData();
        formData2.append('plugin_file', fs.createReadStream(zipFilePath));
        
        const response2 = await axios.post(
          `${this.baseURL}/wp-json/bricks/v1/install-plugin`,
          formData2,
          {
            auth: {
              username: this.username,
              password: this.password
            },
            headers: {
              ...formData2.getHeaders()
            },
            maxContentLength: Infinity,
            maxBodyLength: Infinity
          }
        );
        
        return { success: true, data: response2.data };
      } catch (error2) {
        return { 
          success: false, 
          error: error.message,
          details: error.response?.data,
          note: 'May need to use WordPress Filesystem API or FTP access'
        };
      }
    }
  }

  /**
   * Nainstalovat téma ze ZIP souboru
   */
  async installTheme(zipFilePath) {
    try {
      const formData = new FormData();
      formData.append('theme_file', fs.createReadStream(zipFilePath));
      
      const response = await axios.post(
        `${this.baseURL}/wp-json/bricks/v1/install-theme`,
        formData,
        {
          auth: {
            username: this.username,
            password: this.password
          },
          headers: {
            ...formData.getHeaders()
          },
          maxContentLength: Infinity,
          maxBodyLength: Infinity
        }
      );

      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data,
        note: 'May need to use WordPress Filesystem API or FTP access'
      };
    }
  }

  /**
   * Aktivovat plugin
   */
  async activatePlugin(pluginPath) {
    try {
      const response = await this.client.post('/plugins', {
        plugin: pluginPath,
        status: 'active'
      });
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Regenerovat code signatures pro stránku nebo template
   */
  async regenerateSignatures(pageId) {
    try {
      const response = await this.client.post(
        `/bricks/v1/regenerate-signatures/${pageId}`,
        {},
        {
          baseURL: this.baseURL + '/wp-json'
        }
      );
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Získat všechny templates
   */
  async getTemplates(templateType = null) {
    try {
      const params = templateType ? { type: templateType } : {};
      const response = await this.client.get('/bricks/v1/templates', {
        params,
        baseURL: this.baseURL + '/wp-json'
      });
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Vytvořit nebo aktualizovat template
   */
  async createOrUpdateTemplate(title, templateType, content, templateId = null) {
    try {
      // Příprava obsahu - stejně jako u pages
      let contentString;
      
      if (typeof content === 'string') {
        contentString = content;
      } else if (Array.isArray(content)) {
        contentString = JSON.stringify(content);
      } else if (content && typeof content === 'object' && content.content) {
        contentString = JSON.stringify(content.content);
      } else {
        contentString = JSON.stringify(content);
      }
      
      // Pokud máme ID, použít přímo WordPress REST API pro aktualizaci meta (stejně jako pages)
      if (templateId) {
        try {
          // Dekódovat obsah, pokud je to string
          let contentToSave = typeof contentString === 'string' ? JSON.parse(contentString) : contentString;
          
          // Pokud má objekt "content" pole, extrahovat
          if (contentToSave && typeof contentToSave === 'object' && contentToSave.content && Array.isArray(contentToSave.content)) {
            contentToSave = contentToSave.content;
          }
          
          // Zkusit použít custom endpoint
          try {
            const response = await this.client.post(
              `/bricks/v1/template/${templateId}/content`,
              { content: contentString },
              {
                baseURL: this.baseURL + '/wp-json'
              }
            );
            
            if (response.data && response.data.success) {
              return { success: true, data: { id: templateId, ...response.data } };
            }
          } catch (endpointError) {
            // Pokud custom endpoint selhal, použít přímo WordPress REST API
            // Získat template type z existujícího template
            const templateResult = await this.getTemplates();
            const existingTemplate = templateResult.success 
              ? templateResult.data.find(t => t.id === templateId)
              : null;
            const templateType = existingTemplate?.type || 'header';
            
            // Aktualizovat template přes WordPress REST API s meta daty
            const response = await this.client.post(`/wp/v2/bricks_template/${templateId}`, {
              meta: {
                '_bricks_template_type': templateType,
                '_bricks_page_content': contentToSave,
                '_bricks_page_content_2': contentToSave,
                '_bricks_editor_mode': 'bricks',
                '_bricks_page_content_type': 'bricks',
                '_bricks_template_active': true,
                '_bricks_template_conditions': []
              }
            });
            
            return { success: true, data: { id: templateId, ...response.data } };
          }
        } catch (error) {
          // Pokud vše selhalo, použít starý způsob
          console.log('   ⚠️  Aktualizace přes REST API selhala, používám starý způsob...');
        }
      }
      
      // Použít starý způsob (vytvoření/aktualizace přes /bricks/v1/template)
      const payload = {
        title: title,
        type: templateType,
        content: contentString
      };
      
      if (templateId) {
        payload.id = templateId;
      }
      
      const response = await this.client.post(
        '/bricks/v1/template',
        payload,
        {
          baseURL: this.baseURL + '/wp-json'
        }
      );
      
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Získat seznam témat
   */
  async getThemes() {
    try {
      const response = await this.client.get('/bricks/v1/themes', {
        baseURL: this.baseURL + '/wp-json'
      });
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Aktivovat téma
   */
  async activateTheme(themeSlug) {
    try {
      const response = await this.client.post(
        '/bricks/v1/activate-theme',
        { theme: themeSlug },
        {
          baseURL: this.baseURL + '/wp-json'
        }
      );
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Smazat téma
   */
  async deleteTheme(themeSlug) {
    try {
      const response = await this.client.post(
        '/bricks/v1/delete-theme',
        { theme: themeSlug },
        {
          baseURL: this.baseURL + '/wp-json'
        }
      );
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Aktualizovat téma
   */
  async updateTheme(themeSlug = 'bricks') {
    try {
      // Nejdřív zkusit custom endpoint
      try {
        const response = await this.client.post(
          '/bricks/v1/update-theme',
          { theme: themeSlug },
          {
            baseURL: this.baseURL + '/wp-json'
          }
        );
        return { success: true, data: response.data };
      } catch (customError) {
        // Pokud custom endpoint nefunguje, zkusit admin-ajax.php (WordPress standardní způsob)
        console.log('   ⚠️  Custom endpoint nefunguje, zkouším admin-ajax.php...');
        try {
          // Získat nonce z WordPress (potřebujeme pro bezpečnost)
          // Zkusit získat nonce z themes.php stránky nebo použít obecný nonce
          const ajaxResponse = await axios.post(
            `${this.baseURL}/wp-admin/admin-ajax.php`,
            new URLSearchParams({
              action: 'update-theme',
              theme: themeSlug,
              _wpnonce: await this.getUpdateNonce() || ''
            }),
            {
              auth: {
                username: this.username,
                password: this.password
              },
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              }
            }
          );
          
          // WordPress admin-ajax.php vrací JSON nebo HTML
          if (ajaxResponse.data && typeof ajaxResponse.data === 'object') {
            return { success: true, data: ajaxResponse.data };
          } else if (ajaxResponse.data && ajaxResponse.data.includes('success') || ajaxResponse.data.includes('updated')) {
            // Pokud vrací HTML s úspěchem, zkusit získat verzi tématu
            const themesResult = await this.getThemes();
            if (themesResult.success) {
              const updatedTheme = themesResult.data?.find(t => t.slug === themeSlug || t.stylesheet === themeSlug);
              return { 
                success: true, 
                data: { 
                  message: 'Téma aktualizováno',
                  theme: themeSlug,
                  new_version: updatedTheme?.version || 'unknown'
                }
              };
            }
            return { success: true, data: { message: 'Téma aktualizováno' } };
          }
          
          return { 
            success: false, 
            error: 'Neznámá odpověď z admin-ajax.php',
            details: ajaxResponse.data
          };
        } catch (ajaxError) {
          // Pokud ani admin-ajax.php nefunguje, vrátit původní chybu
          return { 
            success: false, 
            error: customError.message,
            details: customError.response?.data,
            ajaxError: ajaxError.message,
            note: 'Ujistěte se, že bricks-api-endpoint.php plugin je aktivní a permalinks jsou aktualizovány'
          };
        }
      }
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Získat nonce pro aktualizaci tématu
   * WordPress potřebuje nonce pro bezpečnost při aktualizaci
   */
  async getUpdateNonce() {
    try {
      // Zkusit získat nonce z themes.php stránky
      const themesPage = await axios.get(
        `${this.baseURL}/wp-admin/themes.php`,
        {
          auth: {
            username: this.username,
            password: this.password
          }
        }
      );
      
      // Hledat nonce v HTML (WordPress obvykle používá data-wp-nonce nebo hidden input)
      const html = themesPage.data;
      const nonceMatch = html.match(/update-theme[^"']*["']([^"']+)["']/) || 
                        html.match(/name=["']_wpnonce["'] value=["']([^"']+)["']/) ||
                        html.match(/data-wp-nonce=["']([^"']+)["']/);
      
      if (nonceMatch && nonceMatch[1]) {
        return nonceMatch[1];
      }
      
      // Pokud nenajdeme specifický nonce, zkusit získat obecný update nonce
      // WordPress může použít update_nonce z wp_localize_script
      const updateNonceMatch = html.match(/update_nonce["']:\s*["']([^"']+)["']/);
      if (updateNonceMatch && updateNonceMatch[1]) {
        return updateNonceMatch[1];
      }
      
      return null;
    } catch (error) {
      // Pokud se nepodaří získat nonce, vrátit null (zkusit bez nonce)
      return null;
    }
  }

  /**
   * Smazat stránku
   */
  async deletePage(pageId, force = true) {
    try {
      const response = await this.client.delete(`/pages/${pageId}`, {
        params: {
          force: force // true = trvale smazat, false = přesunout do koše
        }
      });
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data 
      };
    }
  }

  /**
   * Smazat template
   */
  async deleteTemplate(templateId, force = true) {
    try {
      // Metoda 1: DELETE endpoint
      try {
        const response = await this.client.delete(
          `/bricks/v1/template/${templateId}`,
          {
            params: { force: force },
            baseURL: this.baseURL + '/wp-json'
          }
        );
        return { success: true, data: response.data };
      } catch (deleteError) {
        // Metoda 2: POST endpoint s /delete
        try {
          const response = await this.client.post(
            `/bricks/v1/template/${templateId}/delete`,
            { force: force },
            {
              baseURL: this.baseURL + '/wp-json'
            }
          );
          return { success: true, data: response.data };
        } catch (postError) {
          // Metoda 3: Alternativní jednodušší endpoint
          try {
            const response = await this.client.post(
              `/bricks/v1/delete-template`,
              { id: templateId, force: force },
              {
                baseURL: this.baseURL + '/wp-json'
              }
            );
            return { success: true, data: response.data };
          } catch (altError) {
            // Metoda 4: Hromadné smazání (zkusit i pro jeden template)
            try {
              const response = await this.client.post(
                `/bricks/v1/templates/delete`,
                { ids: [templateId], force: force },
                {
                  baseURL: this.baseURL + '/wp-json'
                }
              );
              if (response.data.success && response.data.deleted_count > 0) {
                return { success: true, data: response.data };
              } else {
                throw new Error('Hromadné smazání selhalo');
              }
            } catch (bulkError) {
              return { 
                success: false, 
                error: deleteError.message,
                details: deleteError.response?.data || postError.response?.data || altError.response?.data || bulkError.response?.data
              };
            }
          }
        }
      }
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data
      };
    }
  }

  /**
   * Hromadné smazání templates
   */
  async deleteTemplatesBulk(templateIds, force = true) {
    try {
      const response = await this.client.post(
        `/bricks/v1/templates/delete`,
        { ids: templateIds, force: force },
        {
          baseURL: this.baseURL + '/wp-json'
        }
      );
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data
      };
    }
  }

  /**
   * Aktualizovat Bricks licenční klíč
   */
  async updateBricksLicense(licenseKey) {
    try {
      // Bricks licenční klíč se ukládá v options
      // Musíme použít custom endpoint nebo aktualizovat přes options API
      const response = await this.client.post('/options', {
        bricks_license_key: licenseKey
      });
      return { success: true, data: response.data };
    } catch (error) {
      // Alternativní metoda: použít wp_options přes databázi
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data,
        note: 'May need custom endpoint or database access to update license key'
      };
    }
  }

  /**
   * Konfigurovat Bricks Settings (Code Execution + Post Types)
   */
  async configureBricksSettings() {
    try {
      const response = await this.client.post(
        '/bricks/v1/configure-settings',
        {},
        {
          baseURL: this.baseURL + '/wp-json'
        }
      );
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data
      };
    }
  }

  /**
   * Nastavit WordPress Reading (Static page)
   */
  async setReadingSettings(pageId) {
    try {
      const response = await this.client.post(
        '/bricks/v1/set-reading',
        { page_id: pageId },
        {
          baseURL: this.baseURL + '/wp-json'
        }
      );
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data,
        note: 'Možná je potřeba nastavit ručně: Settings → Reading → Static page'
      };
    }
  }

  /**
   * Aktivovat Bricks licenci
   */
  async activateBricksLicense(licenseKey) {
    try {
      const response = await this.client.post(
        '/bricks/v1/activate-license',
        { license_key: licenseKey },
        {
          baseURL: this.baseURL + '/wp-json'
        }
      );
      return { success: true, data: response.data };
    } catch (error) {
      return { 
        success: false, 
        error: error.message,
        details: error.response?.data,
        note: 'Možná je potřeba aktivovat licenci ručně v Bricks Settings'
      };
    }
  }
}

