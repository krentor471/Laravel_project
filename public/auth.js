// Функция для синхронизации sessionStorage с сервером
function syncAuthState() {
    const authSection = document.querySelector('.auth-section');
    if (!authSection) return;

    // Проверяем, есть ли пользователь в сессии (на сервере)
    const userNameElement = authSection.querySelector('.user-name');

    if (userNameElement) {
        // Пользователь залогинен на сервере - сохраняем в sessionStorage
        const userName = userNameElement.textContent;
        sessionStorage.setItem('user_name', userName);
        sessionStorage.setItem('user_logged_in', 'true');
    } else {
        // Пользователь не залогинен - очищаем sessionStorage
        sessionStorage.removeItem('user_name');
        sessionStorage.removeItem('user_logged_in');
    }
}

// Запускаем синхронизацию при загрузке страницы
document.addEventListener('DOMContentLoaded', syncAuthState);
