/*
 * DCP Leaderboard Plugin
 * Copyright (C) 2025 Joseph Galea
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

function showLoadingSpinner() {
    const spinnerContainer = document.createElement('div');
    spinnerContainer.id = 'spinner-container';
    spinnerContainer.style.cssText = `
      position: fixed; /* Stay in place */
      top: 50%; /* Center vertically */
      left: 50%; /* Center horizontally */
      transform: translate(-50%, -50%); /* Adjust for centering */
      z-index: 1000; /* Ensure it's on top */
    `;
  
    const spinner = document.createElement('div');
    spinner.id = 'spinner';
    spinner.style.cssText = `
      border: 4px solid #f3f3f3; /* Light grey */
      border-top: 4px solid #3498db; /* Blue */
      border-radius: 50%;
      width: 50px;
      height: 50px;
      animation: spin 2s linear infinite; /* Animate the spinner */
    `;
  
    const style = document.createElement('style'); // Add animation CSS
    style.textContent = `
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
    `;
  
    spinnerContainer.appendChild(spinner);
    document.head.appendChild(style); // Add styles to head
    document.body.appendChild(spinnerContainer);
  }
  
  function hideLoadingSpinner() {
    const spinnerContainer = document.getElementById('spinner-container');
    if (spinnerContainer) {
      spinnerContainer.remove();
    }
  }

function onMySubmitClick (event){
    event.preventDefault();
    showLoadingSpinner();
    const remote_script_params = window.remote_script_params;

    if (!remote_script_params || !remote_script_params.nonce) {
        console.error("Nonce not found. Make sure wp_localize_script is used correctly.");
        return; // Stop execution if nonce is missing
    }

    const nonce = remote_script_params.nonce;

    fetch(window.location.origin + '/wp-json/dcpleaderboard/v1/endpoint/admin/clubs', {
      method: 'GET',
      headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': nonce,
      },
      credentials: 'include' // If needed for CORS and cookies
    }) 
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        console.log('Response received:', data);
        setTimeout(hideLoadingSpinner(),5000);
      })
      .catch(error => {
        console.error('Error fetching data:', error);
        setTimeout(hideLoadingSpinner(),2000);
      });
};