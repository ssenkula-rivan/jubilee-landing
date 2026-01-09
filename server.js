const express = require('express');
const multer = require('multer');
const nodemailer = require('nodemailer');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static('.'));

// File upload config
const storage = multer.memoryStorage();
const upload = multer({
    storage: storage,
    limits: { fileSize: 10 * 1024 * 1024 }, // 10MB
    fileFilter: (req, file, cb) => {
        const allowed = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (allowed.includes(file.mimetype)) {
            cb(null, true);
        } else {
            cb(new Error('Invalid file type. Only PDF, JPG, PNG allowed'));
        }
    }
});

// Email transporter - Configure with your SMTP
const transporter = nodemailer.createTransport({
    host: process.env.SMTP_HOST || 'smtp.gmail.com',
    port: process.env.SMTP_PORT || 587,
    secure: false,
    auth: {
        user: process.env.SMTP_USER || 'your-email@gmail.com',
        pass: process.env.SMTP_PASS || 'your-app-password'
    }
});

const TO_EMAIL = 'george.kaggo@jubileeuganda.com';

// Job Application Endpoint
app.post('/api/apply', upload.single('cv'), async (req, res) => {
    try {
        const { fullName, email, phone, education, experience, motivation } = req.body;
        
        if (!fullName || !email || !phone || !education || !experience) {
            return res.json({ success: false, message: 'Please fill all required fields' });
        }

        if (!req.file) {
            return res.json({ success: false, message: 'Please upload your CV' });
        }

        const mailOptions = {
            from: process.env.SMTP_USER || 'noreply@jubileeuganda.com',
            to: TO_EMAIL,
            replyTo: email,
            subject: `New Job Application - Sales Agent: ${fullName}`,
            html: `
                <div style="font-family: Arial, sans-serif;">
                    <div style="background: #c41e3a; color: white; padding: 20px; text-align: center;">
                        <h2>New Job Application - Sales Agent</h2>
                    </div>
                    <div style="padding: 20px;">
                        <p><strong>Full Name:</strong> ${fullName}</p>
                        <p><strong>Email:</strong> ${email}</p>
                        <p><strong>Phone:</strong> ${phone}</p>
                        <p><strong>Education:</strong> ${education}</p>
                        <p><strong>Experience:</strong> ${experience}</p>
                        <p><strong>Motivation:</strong><br>${motivation || 'Not provided'}</p>
                        <p><strong>CV:</strong> ${req.file.originalname} (attached)</p>
                    </div>
                    <div style="background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px;">
                        Submitted via Jubilee Health Insurance Landing Page
                    </div>
                </div>
            `,
            attachments: [{
                filename: req.file.originalname,
                content: req.file.buffer
            }]
        };

        await transporter.sendMail(mailOptions);
        res.json({ success: true, message: 'Application submitted successfully! We will contact you soon.' });

    } catch (error) {
        console.error('Error:', error);
        res.json({ success: false, message: 'Failed to send application. Please try again.' });
    }
});

// Insurance Inquiry Endpoint
app.post('/api/insurance', upload.none(), async (req, res) => {
    try {
        const { fullName, email, phone, insuranceType, corporatePlan, smePlan, personalPlan, ageCategory, numberOfPeople, motivation } = req.body;
        
        if (!fullName || !email || !phone || !insuranceType) {
            return res.json({ success: false, message: 'Please fill all required fields' });
        }

        let selectedPlan = '';
        if (insuranceType === 'Corporate') selectedPlan = corporatePlan || '';
        else if (insuranceType === 'SME') selectedPlan = smePlan || '';
        else if (insuranceType === 'Personal') {
            selectedPlan = personalPlan || '';
            if (ageCategory) selectedPlan += ` - Age: ${ageCategory}`;
        }

        const mailOptions = {
            from: process.env.SMTP_USER || 'noreply@jubileeuganda.com',
            to: TO_EMAIL,
            replyTo: email,
            subject: `New Insurance Inquiry: ${fullName} - ${insuranceType}`,
            html: `
                <div style="font-family: Arial, sans-serif;">
                    <div style="background: #c41e3a; color: white; padding: 20px; text-align: center;">
                        <h2>New Insurance Inquiry</h2>
                        <p>${insuranceType} Insurance</p>
                    </div>
                    <div style="padding: 20px;">
                        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                            <strong>Insurance Category:</strong> ${insuranceType}<br>
                            <strong>Selected Plan:</strong> ${selectedPlan}<br>
                            <strong>Number of People:</strong> ${numberOfPeople || 'Not specified'}
                        </div>
                        <h3 style="color: #c41e3a;">Contact Information</h3>
                        <p><strong>Full Name:</strong> ${fullName}</p>
                        <p><strong>Email:</strong> <a href="mailto:${email}">${email}</a></p>
                        <p><strong>Phone:</strong> <a href="tel:${phone}">${phone}</a></p>
                        <h3 style="color: #c41e3a;">Additional Message</h3>
                        <p>${motivation || 'Not provided'}</p>
                    </div>
                    <div style="background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px;">
                        Submitted via Jubilee Health Insurance Landing Page
                    </div>
                </div>
            `
        };

        await transporter.sendMail(mailOptions);
        res.json({ success: true, message: 'Inquiry submitted successfully! Our team will contact you within 24 hours.' });

    } catch (error) {
        console.error('Error:', error);
        res.json({ success: false, message: 'Failed to send inquiry. Please try again.' });
    }
});

app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});
