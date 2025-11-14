/**
 * --------------------------------------------------------------------------
 * CoreUI PRO Bootstrap Admin Template openstreetmap.js
 * License (https://coreui.io/pro/license/)
 * --------------------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', function() {
  try {
    // Clear the loading placeholder
    const mapElement = document.getElementById('map');
    if (mapElement) {
      mapElement.innerHTML = '';
    }

    // Initialize the map centered at Stanford University
    const map = L.map('map').setView([37.431489, -122.163719], 11);

    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Define locations (same as before but for OpenStreetMap)
    const locations = [
      {
        lat: 37.431489,
        lng: -122.163719,
        label: 'S',
        title: 'Stanford',
        www: 'https://www.stanford.edu/'
      },
      {
        lat: 37.394694,
        lng: -122.150333,
        label: 'T',
        title: 'Tesla',
        www: 'https://www.tesla.com/'
      },
      {
        lat: 37.331681,
        lng: -122.0301,
        label: 'A',
        title: 'Apple',
        www: 'https://www.apple.com/'
      },
      {
        lat: 37.484722,
        lng: -122.148333,
        label: 'F',
        title: 'Facebook',
        www: 'https://www.facebook.com/'
      }
    ];

    // Add markers for each location
    locations.forEach(location => {
      const marker = L.marker([location.lat, location.lng]).addTo(map);

      // Create popup content
      const popupContent = `<a href="${location.www}" target="_blank"><strong>${location.title}</strong></a>`;
      marker.bindPopup(popupContent);

      // Optional: Add a tooltip
      marker.bindTooltip(location.title);
    });

    console.log('OpenStreetMap initialized successfully');

  } catch (error) {
    console.error('Error initializing OpenStreetMap:', error);
    const mapElement = document.getElementById('map');
    if (mapElement) {
      mapElement.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;"><h4>Map Loading Error</h4><p>There was an error loading the OpenStreetMap. Please check your internet connection.</p></div>';
    }
  }
});