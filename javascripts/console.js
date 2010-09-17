try { console.debug('console init...'); } catch(e) { console = { debug: function(){}, log: function(){} } }
