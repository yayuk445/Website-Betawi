window.addEventListener('DOMContentLoaded', () => {
  const startBtn = document.getElementById('startGameBtn');
  const backsound = document.getElementById('backsound');
  let gameStarted = false;

  startBtn.addEventListener('click', () => {
    gameStarted = true;
    startBtn.style.display = 'none';
    try { 
      backsound.volume = 0.2; 
      backsound.play().catch(()=>{}); 
    } catch(e){ 
      console.warn(e); 
    }
    window.isGameStarted = () => true;
  });

  window.isGameStarted = () => gameStarted;
});

/**
 *
 * @param {string} id
 */
function playSound(id) {
  const audio = document.getElementById('audio-' + id);
  if (!audio) return;
  try { 
    audio.currentTime = 0; 
    audio.volume = 0.6; 
    audio.play().catch(()=>{}); 
  } catch(e){}
}

/**
 * 
 * @param {HTMLElement} el 
 */
function flashWrong(el) {
  el.style.transition = 'transform 120ms';
  el.style.transform += ' translateX(-10px)';
  setTimeout(() => el.style.transform = el.style.transform.replace(' translateX(-10px)',''), 140);
  setTimeout(() => el.style.transform += ' translateX(10px)', 260);
  setTimeout(() => el.style.transform = el.style.transform.replace(' translateX(10px)',''), 380);
}

/**
 * 
 * @param {HTMLElement} el 
 */
function popElement(el) {
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 600);
}


function checkOrientation() {
  const overlay = document.getElementById('rotate-overlay');
  const gameRoot = document.getElementById('game-root');

  if (!overlay || !gameRoot) return;

  if (window.innerHeight > window.innerWidth) {
    overlay.style.display = 'flex';
    gameRoot.style.display = 'none';
  } else {
    overlay.style.display = 'none';
    gameRoot.style.display = 'flex';
  }
}

window.addEventListener('DOMContentLoaded', () => {
  checkOrientation();
  window.addEventListener('resize', checkOrientation);
});
