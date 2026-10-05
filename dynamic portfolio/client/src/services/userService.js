// userService.js
export const fetchUserData = async (userId) => {
    try {
        const response = await fetch(`/server/api/user.php?id=${userId}`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching user data:', error);
        throw error;
    }
};

export const authenticateUser = async (credentials) => {
    try {
        const response = await fetch('/server/api/user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(credentials),
        });
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error authenticating user:', error);
        throw error;
    }
};