/**
 * Saved Vehicles Manager
 * Gestionează salvarea, afișarea și gestionarea mașinilor salvate în localStorage
 */
class SavedVehiclesManager {
  constructor() {
    this.storageKey = 'savedVehicles';
    this.savedVehicles = this.loadFromStorage();
    this.init();
  }

  /**
   * Inițializează managerul
   */
  init() {
    this.updateUI();
    this.bindEvents();
    this.updateCount();
  }

  /**
   * Încarcă mașinile salvate din localStorage
   */
  loadFromStorage() {
    try {
      const saved = localStorage.getItem(this.storageKey);
      return saved ? JSON.parse(saved) : [];
    } catch (error) {
      console.error('Eroare la încărcarea mașinilor salvate:', error);
      return [];
    }
  }

  /**
   * Salvează mașinile în localStorage
   */
  saveToStorage() {
    try {
      localStorage.setItem(this.storageKey, JSON.stringify(this.savedVehicles));
    } catch (error) {
      console.error('Eroare la salvarea mașinilor:', error);
    }
  }

  /**
   * Adaugă o mașină la lista de mașini salvate
   */
  addVehicle(vehicleData) {
    // Verifică dacă mașina există deja
    const existingIndex = this.savedVehicles.findIndex(v => v.id === vehicleData.id);
    
    if (existingIndex !== -1) {
      // Actualizează datele existente
      this.savedVehicles[existingIndex] = { ...this.savedVehicles[existingIndex], ...vehicleData };
    } else {
      // Adaugă mașina nouă
      this.savedVehicles.push({
        ...vehicleData,
        savedAt: new Date().toISOString(),
        notes: vehicleData.notes || ''
      });
    }
    
    this.saveToStorage();
    this.updateUI();
    this.updateCount();
    this.showNotification('Mașina a fost salvată!', 'success');
  }

  /**
   * Elimină o mașină din lista de mașini salvate
   */
  removeVehicle(vehicleId) {
    const index = this.savedVehicles.findIndex(v => v.id === vehicleId);
    if (index !== -1) {
      this.savedVehicles.splice(index, 1);
      this.saveToStorage();
      this.updateUI();
      this.updateCount();
      this.showNotification('Mașina a fost eliminată din lista de salvate!', 'info');
    }
  }

  /**
   * Actualizează notele pentru o mașină
   */
  updateVehicleNotes(vehicleId, notes) {
    const vehicle = this.savedVehicles.find(v => v.id === vehicleId);
    if (vehicle) {
      vehicle.notes = notes;
      this.saveToStorage();
      this.updateUI();
      this.showNotification('Notele au fost actualizate!', 'success');
    }
  }

  /**
   * Șterge toate mașinile salvate
   */
  clearAll() {
    if (confirm('Ești sigur că vrei să ștergi toate mașinile salvate?')) {
      this.savedVehicles = [];
      this.saveToStorage();
      this.updateUI();
      this.updateCount();
      this.showNotification('Toate mașinile au fost șterse!', 'info');
    }
  }

  /**
   * Verifică dacă o mașină este salvată
   */
  isVehicleSaved(vehicleId) {
    return this.savedVehicles.some(v => v.id === vehicleId);
  }

  /**
   * Actualizează interfața utilizator
   */
  updateUI() {
    this.updateDropdown();
    this.updateSavedVehiclesPage();
    this.updateSaveButtons();
  }

  /**
   * Actualizează dropdown-ul din navigație
   */
  updateDropdown() {
    const dropdownList = document.getElementById('savedVehiclesList');
    const countElement = document.getElementById('savedVehiclesCount');
    
    if (!dropdownList || !countElement) return;

    if (this.savedVehicles.length === 0) {
      dropdownList.innerHTML = '<div class="px-3 py-2 text-muted small">Nu ai mașini salvate</div>';
      countElement.style.display = 'none';
    } else {
      countElement.style.display = 'inline';
      countElement.textContent = this.savedVehicles.length;
      
      const vehiclesHtml = this.savedVehicles.slice(0, 3).map(vehicle => `
        <li>
          <a class="dropdown-item d-flex align-items-center gap-2" href="/vehicles/${vehicle.slug}">
            <img src="${vehicle.image || 'https://via.placeholder.com/40x30/f8f9fa/6c757d?text=No+img'}" 
                 alt="${vehicle.title}" class="rounded" style="width: 40px; height: 30px; object-fit: cover;">
            <div class="flex-grow-1">
              <div class="small fw-semibold">${vehicle.title}</div>
              <div class="small text-muted">€ ${this.formatPrice(vehicle.price)}</div>
            </div>
          </a>
        </li>
      `).join('');
      
      dropdownList.innerHTML = vehiclesHtml;
    }
  }

  /**
   * Actualizează pagina de mașini salvate
   */
  updateSavedVehiclesPage() {
    const container = document.getElementById('savedVehiclesContainer');
    const noVehiclesDiv = document.getElementById('noSavedVehicles');
    
    if (!container) return;

    if (this.savedVehicles.length === 0) {
      container.innerHTML = '';
      if (noVehiclesDiv) noVehiclesDiv.style.display = 'block';
    } else {
      if (noVehiclesDiv) noVehiclesDiv.style.display = 'none';
      
      const vehiclesHtml = this.savedVehicles.map(vehicle => this.createVehicleCard(vehicle)).join('');
      container.innerHTML = vehiclesHtml;
      
      // Re-bind events pentru butoanele nou create
      this.bindCardEvents();
    }
  }

  /**
   * Creează HTML-ul pentru un card de mașină salvată
   */
  createVehicleCard(vehicle) {
    return `
      <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
        <div class="card h-100 saved-vehicle-card" data-vehicle-id="${vehicle.id}">
          <img src="${vehicle.image || 'https://via.placeholder.com/400x300/f8f9fa/6c757d?text=Fără+imagine'}" 
               class="card-img-top" alt="${vehicle.title}"
               onerror="this.src='https://via.placeholder.com/400x300/f8f9fa/6c757d?text=Fără+imagine'">
          
          <div class="card-body">
            <h6 class="card-title">${vehicle.title}</h6>
            <div class="vehicle-info mb-2">
              <div class="price">€ ${this.formatPrice(vehicle.price)}</div>
            </div>
            
            <div class="card-actions">
              <div class="d-flex gap-1">
                <button type="button" class="btn btn-sm btn-edit edit-notes-btn" 
                        data-vehicle-id="${vehicle.id}" title="Editează notele">
                  <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-sm btn-remove remove-vehicle-btn" 
                        data-vehicle-id="${vehicle.id}" title="Elimină din salvate">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
              <a href="/vehicles/${vehicle.slug}" class="btn btn-sm btn-primary">Detalii</a>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  /**
   * Actualizează numărul de mașini salvate
   */
  updateCount() {
    const countElement = document.getElementById('savedVehiclesCount');
    if (countElement) {
      countElement.textContent = this.savedVehicles.length;
      countElement.style.display = this.savedVehicles.length > 0 ? 'inline' : 'none';
    }
  }

  /**
   * Actualizează starea butoanelor de salvare
   */
  updateSaveButtons() {
    // Actualizează butoanele din catalog
    document.querySelectorAll('.save-vehicle-btn').forEach(btn => {
      const vehicleId = btn.dataset.vehicleId;
      if (this.isVehicleSaved(vehicleId)) {
        btn.innerHTML = '<i class="bi bi-bookmark-heart"></i>';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-primary');
        btn.title = 'Mașina salvată';
      } else {
        btn.innerHTML = '<i class="bi bi-bookmark-heart"></i>';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline-primary');
        btn.title = 'Salvează mașina';
      }
    });

    // Actualizează butonul din pagina individuală
    const saveBtn = document.getElementById('saveVehicleBtn');
    if (saveBtn) {
      const vehicleId = saveBtn.dataset.vehicleId;
      if (this.isVehicleSaved(vehicleId)) {
        saveBtn.innerHTML = '<i class="bi bi-bookmark-heart-fill me-2"></i><span id="saveVehicleText">Mașina salvată</span>';
        saveBtn.classList.remove('btn-outline-primary');
        saveBtn.classList.add('btn-primary');
      } else {
        saveBtn.innerHTML = '<i class="bi bi-bookmark-heart me-2"></i><span id="saveVehicleText">Salvează mașina</span>';
        saveBtn.classList.remove('btn-primary');
        saveBtn.classList.add('btn-outline-primary');
      }
    }
  }

  /**
   * Formatează prețul
   */
  formatPrice(price) {
    if (typeof price === 'string') {
      const numericPrice = price.replace(/[^\d]/g, '');
      return numericPrice ? parseInt(numericPrice).toLocaleString() : '0';
    }
    return price ? price.toLocaleString() : '0';
  }

  /**
   * Afișează o notificare
   */
  showNotification(message, type = 'info') {
    // Creează o notificare simplă
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Elimină notificarea după 3 secunde
    setTimeout(() => {
      if (notification.parentNode) {
        notification.remove();
      }
    }, 3000);
  }

  /**
   * Leagă evenimentele pentru butoane
   */
  bindEvents() {
    // Butoane de salvare din catalog și paginile individuale
    document.addEventListener('click', (e) => {
      if (e.target.closest('.save-vehicle-btn')) {
        const btn = e.target.closest('.save-vehicle-btn');
        const vehicleData = {
          id: btn.dataset.vehicleId,
          title: btn.dataset.vehicleTitle,
          slug: btn.dataset.vehicleSlug,
          price: btn.dataset.vehiclePrice,
          image: btn.dataset.vehicleImage
        };
        
        if (this.isVehicleSaved(vehicleData.id)) {
          this.removeVehicle(vehicleData.id);
          btn.innerHTML = '<i class="bi bi-bookmark-heart"></i>';
          btn.classList.remove('btn-primary');
          btn.classList.add('btn-outline-primary');
        } else {
          this.addVehicle(vehicleData);
          btn.innerHTML = '<i class="bi bi-bookmark-heart-fill"></i>';
          btn.classList.remove('btn-outline-primary');
          btn.classList.add('btn-primary');
        }
      }
    });

    // Butonul de salvare din pagina individuală
    document.addEventListener('click', (e) => {
      if (e.target.closest('#saveVehicleBtn')) {
        const btn = e.target.closest('#saveVehicleBtn');
        const vehicleData = {
          id: btn.dataset.vehicleId,
          title: btn.dataset.vehicleTitle,
          slug: window.location.pathname.split('/').pop(),
          price: document.querySelector('.display-6')?.textContent?.replace(/[^\d]/g, '') || '0',
          image: document.querySelector('.vehicle-gallery .ratio img')?.src || ''
        };
        
        if (this.isVehicleSaved(vehicleData.id)) {
          this.removeVehicle(vehicleData.id);
          btn.innerHTML = '<i class="bi bi-bookmark-heart me-2"></i><span id="saveVehicleText">Salvează mașina</span>';
          btn.classList.remove('btn-primary');
          btn.classList.add('btn-outline-primary');
        } else {
          this.addVehicle(vehicleData);
          btn.innerHTML = '<i class="bi bi-bookmark-heart-fill me-2"></i><span id="saveVehicleText">Mașina salvată</span>';
          btn.classList.remove('btn-outline-primary');
          btn.classList.add('btn-primary');
        }
      }
    });

    // Link-ul "Vezi toate mașinile salvate"
    document.addEventListener('click', (e) => {
      if (e.target.closest('#viewAllSaved')) {
        e.preventDefault();
        window.location.href = '/saved-vehicles';
      }
    });

    // Butonul "Șterge toate"
    document.addEventListener('click', (e) => {
      if (e.target.closest('#clearAllSaved')) {
        this.clearAll();
      }
    });
  }

  /**
   * Leagă evenimentele pentru cardurile de mașini salvate
   */
  bindCardEvents() {
    // Butoane de eliminare
    document.querySelectorAll('.remove-vehicle-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const vehicleId = e.target.closest('.remove-vehicle-btn').dataset.vehicleId;
        this.removeVehicle(vehicleId);
      });
    });

    // Butoane de editare note
    document.querySelectorAll('.edit-notes-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const vehicleId = e.target.closest('.edit-notes-btn').dataset.vehicleId;
        this.showNotesEditor(vehicleId);
      });
    });
  }

  /**
   * Afișează editorul pentru note
   */
  showNotesEditor(vehicleId) {
    const vehicle = this.savedVehicles.find(v => v.id === vehicleId);
    if (!vehicle) return;

    const notes = prompt('Editează notele pentru această mașină:', vehicle.notes || '');
    if (notes !== null) {
      this.updateVehicleNotes(vehicleId, notes);
    }
  }
}

// Inițializează managerul când DOM-ul este gata
document.addEventListener('DOMContentLoaded', () => {
  window.savedVehiclesManager = new SavedVehiclesManager();
});

// Actualizează UI-ul când pagina se încarcă
window.addEventListener('load', () => {
  if (window.savedVehiclesManager) {
    window.savedVehiclesManager.updateUI();
  }
});
