// contactService.js

export const submitContactForm = async (formData) => {
    try {
        const response = await fetch('/server/api/contact.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData),
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error submitting contact form:', error);
        throw error;
    }
};

export const fetchContactInfo = async () => {
    try {
        const response = await fetch('/server/api/contact.php');
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching contact info:', error);
        throw error;
    }
};