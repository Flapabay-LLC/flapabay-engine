# Profile.tsx Component Documentation

## Overview
The Profile.tsx component is a comprehensive host profile management interface that allows users to view, edit, and verify their profile information. It integrates with two main wizard components: ProfileWizard and VerificationWizard.

## Component Structure

### Main Components
- **Profile.tsx**: Main profile display and editing interface
- **ProfileWizard.tsx**: 6-step profile completion wizard
- **VerificationWizard.tsx**: 3-step identity verification process

## State Management

### Profile.tsx State Variables
```typescript
const [profileInfo, setProfileInfo] = useState({
  name: '',
  email: '',
  phone: '',
  location: '',
  bio: '',
  avatar: null,
  // ... other profile fields
});
const [isEditing, setIsEditing] = useState(false);
const [showProfileWizard, setShowProfileWizard] = useState(false);
const [showVerificationWizard, setShowVerificationWizard] = useState(false);
```

### ProfileWizard.tsx State Variables
```typescript
const [currentStep, setCurrentStep] = useState(1);
const [formData, setFormData] = useState({
  photos: [],
  name: '',
  birthdate: '',
  location: '',
  languages: [],
  shortBio: '',
  bio: '',
  school: '',
  work: '',
  pets: '',
  hobbies: '',
  funFacts: '',
  phone: '',
  email: '',
  expertise: [],
  interests: [],
  askMeAbout: '',
  hostingStyle: '',
  houseRules: '',
  neighborhood: ''
});
```

### VerificationWizard.tsx State Variables
```typescript
const [currentStep, setCurrentStep] = useState(1);
const [otpValue, setOtpValue] = useState('');
const [emailVerified, setEmailVerified] = useState(false);
const [phoneVerified, setPhoneVerified] = useState(false);
const [idFrontFile, setIdFrontFile] = useState(null);
const [idBackFile, setIdBackFile] = useState(null);
const [selfieFile, setSelfieFile] = useState(null);
const [idType, setIdType] = useState('');
```

## UI Components and Functionality

### Profile.tsx Components

#### 1. Avatar Section
- **Component**: Avatar with edit overlay
- **Functionality**: Display profile picture with edit capability
- **Actions**: Click to edit avatar

#### 2. Profile Information Cards
- **Component**: Card containers for different profile sections
- **Sections**:
  - Personal Information (name, email, phone, location)
  - Bio and Description
  - Languages and Skills
  - Hosting Preferences

#### 3. Action Buttons
- **Edit Profile Button**: Toggles edit mode for profile information
- **Save Changes Button**: Saves profile modifications
- **Cancel Button**: Cancels edit mode
- **Complete Profile Button**: Opens ProfileWizard
- **Verify Identity Button**: Opens VerificationWizard

### ProfileWizard.tsx Components (6 Steps)

#### Step 1: Photo Upload
- **Components**: File upload buttons, image preview
- **Functionality**: Upload and manage profile photos
- **Actions**: Upload photos, set primary photo

#### Step 2: Personal Information
- **Components**: Input fields for basic info
- **Fields**: Name, birthdate, location, languages, short bio
- **Validation**: Required field validation

#### Step 3: Detailed Bio
- **Components**: Textarea and input fields
- **Fields**: Full bio, school, work, pets, hobbies, fun facts
- **Functionality**: Rich text input for detailed profile

#### Step 4: Contact Information
- **Components**: Input fields with verification buttons
- **Fields**: Phone number, email address
- **Actions**: Verify phone and email

#### Step 5: Expertise and Interests
- **Components**: Multi-select dropdowns, textarea
- **Fields**: Expertise areas, interests, "ask me about" section
- **Functionality**: Tag-based selection system

#### Step 6: Hosting Details
- **Components**: Textarea fields
- **Fields**: Hosting style, house rules, neighborhood description
- **Functionality**: Final hosting-specific information

### VerificationWizard.tsx Components (3 Steps)

#### Step 1: Identity Document Verification
- **Components**: Dropdown, file upload buttons, camera integration
- **Functionality**: 
  - Select ID type (passport, driver's license, national ID)
  - Upload front and back of ID document
  - Camera capture for real-time photo taking
- **Actions**: File upload, camera capture, document preview

#### Step 2: Selfie and Personal Info
- **Components**: Camera interface, input fields, OTP input
- **Functionality**:
  - Capture selfie for identity verification
  - Enter personal information
  - Phone verification with OTP (demo: "123456")
- **Actions**: Take selfie, verify phone number

#### Step 3: Final Security Steps
- **Components**: Email confirmation, 2FA setup
- **Functionality**:
  - Email verification confirmation
  - Two-factor authentication setup
  - Final security measures
- **Actions**: Confirm email, enable 2FA

## Button Actions and Workflows

### Profile.tsx Buttons

1. **Edit Profile Button**
   - **Action**: `setIsEditing(true)`
   - **Workflow**: Enables edit mode for profile fields

2. **Save Changes Button**
   - **Action**: `handleSaveProfile()`
   - **Workflow**: 
     - Validates form data
     - Calls API to update profile
     - Updates local state
     - Exits edit mode

3. **Cancel Button**
   - **Action**: `setIsEditing(false)`
   - **Workflow**: Reverts changes and exits edit mode

4. **Complete Profile Button**
   - **Action**: `setShowProfileWizard(true)`
   - **Workflow**: Opens ProfileWizard modal

5. **Verify Identity Button**
   - **Action**: `setShowVerificationWizard(true)`
   - **Workflow**: Opens VerificationWizard modal

### ProfileWizard.tsx Buttons

1. **Next Button**
   - **Action**: `handleNext()`
   - **Workflow**: 
     - Validates current step
     - Advances to next step
     - Updates progress indicator

2. **Back Button**
   - **Action**: `handleBack()`
   - **Workflow**: Returns to previous step

3. **Upload Photo Buttons**
   - **Action**: File input trigger
   - **Workflow**: Opens file picker, processes image upload

4. **Verify Phone/Email Buttons**
   - **Action**: Triggers verification process
   - **Workflow**: Sends verification code, validates input

### VerificationWizard.tsx Buttons

1. **Upload ID Buttons**
   - **Action**: `handleIdFrontUpload()`, `handleIdBackUpload()`
   - **Workflow**: File upload and preview for ID documents

2. **Take Photo Button**
   - **Action**: `startCamera()`
   - **Workflow**: Activates camera for document/selfie capture

3. **Capture Selfie Button**
   - **Action**: `captureSelfie()`
   - **Workflow**: Takes selfie photo for verification

4. **Verify Phone Button**
   - **Action**: `verifyPhone()`
   - **Workflow**: Sends OTP, validates with demo code "123456"

5. **Verify Email Button**
   - **Action**: `verifyEmail()`
   - **Workflow**: Sends verification email, confirms receipt

## Data Flow and API Requirements

### Profile Data Flow
```
User Input → Form Validation → State Update → API Call → Response Handling → UI Update
```

### Required API Endpoints

#### Profile Management
- `GET /api/host/profile` - Fetch user profile data
- `PUT /api/host/profile` - Update profile information
- `POST /api/host/profile/avatar` - Upload profile avatar
- `GET /api/host/profile/verification-status` - Check verification status

#### Profile Wizard
- `POST /api/profile/photos` - Upload profile photos
- `PUT /api/profile/personal-info` - Update personal information
- `PUT /api/profile/bio` - Update bio and detailed information
- `POST /api/profile/contact-verification` - Verify contact information
- `PUT /api/profile/expertise` - Update expertise and interests
- `PUT /api/profile/hosting-details` - Update hosting information

#### Verification Wizard
- `POST /api/verification/documents` - Upload ID documents
- `POST /api/verification/selfie` - Upload selfie photo
- `POST /api/verification/phone` - Verify phone number
- `POST /api/verification/email` - Verify email address
- `POST /api/verification/2fa` - Setup two-factor authentication
- `GET /api/verification/status` - Check verification progress

### Data Models

#### Profile Model
```typescript
interface ProfileData {
  id: string;
  name: string;
  email: string;
  phone: string;
  location: string;
  bio: string;
  shortBio: string;
  avatar: string;
  photos: string[];
  languages: string[];
  birthdate: string;
  school: string;
  work: string;
  pets: string;
  hobbies: string;
  funFacts: string;
  expertise: string[];
  interests: string[];
  askMeAbout: string;
  hostingStyle: string;
  houseRules: string;
  neighborhood: string;
  isVerified: boolean;
  verificationStatus: {
    identity: boolean;
    phone: boolean;
    email: boolean;
    documents: boolean;
  };
}
```

#### Verification Model
```typescript
interface VerificationData {
  idType: string;
  idFrontUrl: string;
  idBackUrl: string;
  selfieUrl: string;
  phoneVerified: boolean;
  emailVerified: boolean;
  twoFactorEnabled: boolean;
  verificationDate: string;
  status: 'pending' | 'approved' | 'rejected';
}
```

## Workflow Diagrams

### Profile Management Workflow
```
[Profile View] → [Edit Mode] → [Form Validation] → [API Update] → [Success/Error] → [Profile View]
     ↓
[Complete Profile] → [ProfileWizard] → [6 Steps] → [Profile Complete]
     ↓
[Verify Identity] → [VerificationWizard] → [3 Steps] → [Identity Verified]
```

### ProfileWizard Workflow
```
Step 1: Photos → Step 2: Personal Info → Step 3: Bio → Step 4: Contact → Step 5: Expertise → Step 6: Hosting → Complete
```

### VerificationWizard Workflow
```
Step 1: ID Documents → Step 2: Selfie & Phone → Step 3: Email & 2FA → Verification Complete
```

## Integration Points

1. **Profile ↔ ProfileWizard**: Profile completion status triggers wizard availability
2. **Profile ↔ VerificationWizard**: Verification status affects profile display
3. **ProfileWizard → Profile**: Completed wizard data updates main profile
4. **VerificationWizard → Profile**: Verification status updates profile verification badges

## Security Considerations

- Phone verification uses OTP (currently demo with "123456")
- Email verification requires confirmation
- Document uploads should be encrypted
- Selfie verification for identity matching
- Two-factor authentication for enhanced security
- All API calls should be authenticated and authorized

## Error Handling

- Form validation errors displayed inline
- API error responses handled with user-friendly messages
- File upload errors with retry mechanisms
- Camera access permission handling
- Network connectivity error handling

This documentation provides a comprehensive overview of the Profile.tsx ecosystem, including all components, workflows, and API requirements for successful implementation and maintenance.