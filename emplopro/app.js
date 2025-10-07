function renderApps() {
    const container = document.getElementById('appCards');

    container.innerHTML = '';

    const card1 = document.createElement('div');
    card1.className = 'card eliku-card';
    card1.innerHTML = `
        <div class="card ekilu-card"><img src="ImgAPP/ekilu.jpg" alt=""></div>
        
        <div>
            <h3>EKILU</h3>
            <span class="tag">Planificador de comidas</span>
            <p>Anteriormente coñecido como Nooddle, ekilu permíteche crear un estilo de
            vida equilibrado a través da excelente comida, movemento e atención plena.</p>
            <p><strong>Ventajas:</strong> Personalización, Variedad y adaptabilidad</p>
            <p><strong>Desventajas:</strong> Algunas funciones requieren versión premium</p>
            <p class="rating">⭐ 4.6/5.0</p>
            <a href="https://www.ekilu.com" class="contact-btn" target="_blank">Descargar App</a>
        </div>
    `;
    container.appendChild(card1);

    const card2 = document.createElement('div');
    card2.className = 'card BitePal-card';
    card2.innerHTML = `
        <div class=" card BitePal-card"><img src="ImgAPP/BitePal.png" alt=""></div>
        <div>
            <h3>BitePal</h3>
            <span class="tag">Contador de Calorias</span>
            <p>BitePal es una aplicación de seguimiento de alimentos y nutrición que 
            utiliza inteligencia artificial para simplificar el registro de lo que comes.</p>
            <p><strong>Ventajas:</strong> Función de ayuno intermitente integrada,  Actualizaciones constantes y nuevas funciones</p>
            <p><strong>Desventajas:</strong> Es una aplicacion de pago</p>
            <p class="rating">⭐ 4.8/5.0</p>
            <a href="https://bitepal.app" class="contact-btn" target="_blank">Descargar App</a>
        </div>
    `;
    container.appendChild(card2);

    const card3 = document.createElement('div');
    card3.className = 'card cronometer-card';
    card3.innerHTML = `
        <div class="card cronometer-card"><img src="ImgAPP/cronometer.png" alt=""></div>
        <div>
            <h3>Cronometer</h3>
            <span class="tag">Actividd Fisica</span>
            <p>Cronometer es una aplicación y plataforma de seguimiento nutricional 
            que permite registrar alimentos, calorías, macronutrientes y micronutrientes, además de biometría.</p>
            <p><strong>Ventajas:</strong> Alta precisión y confianza en la base de datos, Personalización de objetivos nutricionales </p>
            <p><strong>Desventajas:</strong> Limitaciones en la versión gratuita, Base de datos no tan extensa para alimentos procesados o es
            pecíficos locales</p>
            <p class="rating">⭐ 4.8/5.0</p>
            <a href="https://cronometer.com" class="contact-btn" target="_blank">Descargar App</a>

        </div>
    `;
    container.appendChild(card3);

}

document.addEventListener("DOMContentLoaded", renderApps);
