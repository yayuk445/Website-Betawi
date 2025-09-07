(function () {
  const hudStep = document.getElementById("step");
  const hasilFinal = document.getElementById("hasil-final");
  const startBtn = document.getElementById("startGameBtn");


  const mainOrder = ["cengkeh", "gula", "jahe", "kayuManis", "kayuSencang", "sereh"];
  let currentStep = 0;


  const bahanMap = {
    cengkeh: "Cengkeh",
    gula: "Gula",
    jahe: "Jahe",
    kayuManis: "Kayu Manis",
    kayuSencang: "Kayu Sencang",
    sereh: "Sereh"
  };

  const hudBahan = document.getElementById("hud-bahan");


  function updateHudBahan() {
    let next = "-";
    if (currentStep < mainOrder.length) next = bahanMap[mainOrder[currentStep]];
    hudBahan.textContent = "Bahan berikut: " + next;
  }

  function highlightNextBahan() {
    document.querySelectorAll(".draggable").forEach(el => el.classList.remove("highlight-bahan"));
    if (currentStep < mainOrder.length) {
      const nextEl = document.getElementById(mainOrder[currentStep]);
      if (nextEl) nextEl.classList.add("highlight-bahan");
    }
  }


  function incrementStep() {
    currentStep++;
    hudStep.textContent = currentStep;
    updateHudBahan();
    highlightNextBahan();

 
    if (currentStep >= mainOrder.length) {
      hasilFinal.style.display = "block";
      try {
        document.getElementById("audio-selesai").play();
      } catch(e) {}
    }
  }


  startBtn.addEventListener("click", () => {
    startBtn.style.display = "none";
    hasilFinal.style.display = "none";
    currentStep = 0;
    hudStep.textContent = "0";
    updateHudBahan();
    highlightNextBahan();
  });


  interact(".draggable").draggable({
    inertia: true,
    autoScroll: true,
    listeners: {
      start(event) {
        const id = event.target.id;
        const nextId = mainOrder[currentStep];
        if (id !== nextId) {
          flashWrong(event.target); 
          event.interactable.stop();
        }
      },
      move(event) {
        const target = event.target;
        const x = (parseFloat(target.getAttribute("data-x")) || 0) + event.dx;
        const y = (parseFloat(target.getAttribute("data-y")) || 0) + event.dy;
        target.style.transform = `translate(${x}px, ${y}px)`;
        target.setAttribute("data-x", x);
        target.setAttribute("data-y", y);
      }
    }
  });

 
  interact("#panci-slot").dropzone({
    accept: ".draggable",
    overlap: 0.5,
    ondrop(event) {
      const dragged = event.relatedTarget;
      const id = dragged.id;

      if (currentStep < mainOrder.length && id === mainOrder[currentStep]) {
        dragged.style.display = "none"; 
        incrementStep();
      }
    },
  });

})();
