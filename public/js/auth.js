/**
 * Helpers para la pantalla de inicio de sesión
 */
function setDemo(user, pass) {
    const userInput = document.getElementById('username');
    const passInput = document.getElementById('password');
    if (userInput && passInput) {
        userInput.value = user;
        passInput.value = pass;
    }
}
