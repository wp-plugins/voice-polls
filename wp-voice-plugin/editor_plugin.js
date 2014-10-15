(function() {
  var w;
  tinymce.create('tinymce.plugins.voicePlugin', {
    init: function(ed, url) {
      ed.addButton('voiceEmbed', {
        title: 'Create a poll',
        image: url + '/20x20.png',
        onclick: function() {
          w = ed.windowManager.open({
            title: 'Create a Poll',
            file: 'https://voicepolls.com/popups/wp/?token=' + voice_token,
            width: 540,
            height: 530,
            inline: 1
          }, {
            plugin_url: url
          });
        }
      });

      window.addEventListener("message", function(e) {
        if (e.data && typeof e.data === 'object') {
          console.log(e)
          if (e.data.created) {
            ed.execCommand('mceInsertContent', false, '[voicepoll id="' + e.data.created + '" question="' + e.data.question + '"]');
            ed.windowManager.close();
          }
          if(e.data.height){
            w.resizeTo(540, e.data.height+ 39);
          }
        }
      });
    },
    createControl: function(n, cm) {
      return null;
    },
    getInfo: function() {
      return {
        longname: 'voice',
        author: 'voice',
        authorurl: 'https://voicepolls.com',
        infourl: 'https://voicepolls.com',
        version: '1.0'
      };
    }
  });
  tinymce.PluginManager.add('voiceEmbed', tinymce.plugins.voicePlugin);


})();