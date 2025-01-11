document.addEventListener('DOMContentLoaded', async function() {
    try {
        const response = await fetch('../handlers/get-user-data.php');
        const userData = await response.json();
        
        if (userData.avatar_url) {
            const headerAvatar = document.querySelector('.avatar-circle');
            if (headerAvatar) {
                headerAvatar.innerHTML = `
                    <img src="${userData.avatar_url}" 
                         alt="Awatar użytkownika"
                         style="width: 100%; height: 100%; object-fit: cover;">`;
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
}); 