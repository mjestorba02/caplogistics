<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

// Require authentication
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) { $input = []; }

function json_response($data, int $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function sanitize_like($term) {
    return str_replace(['%', '_'], ['\\%', '\\_'], $term);
}

try {
    // ===== VENDORS CRUD =====
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'get_vendors';
        
        if ($action === 'get_vendors') {
            $search = isset($_GET['search']) ? trim($_GET['search']) : null;
            $status = isset($_GET['status']) ? trim($_GET['status']) : null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;

            $where = [];
            $params = [];

            if ($search) {
                $where[] = '(vendor_name LIKE :search OR email LIKE :search OR company_name LIKE :search)';
                $params[':search'] = '%' . sanitize_like($search) . '%';
            }

            if ($status) {
                $where[] = 'status = :status';
                $params[':status'] = $status;
            }

            $sql = 'SELECT * FROM vendor_portal_registration';
            if ($where) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= ' ORDER BY created_at DESC';
            
            if ($limit) {
                $sql .= ' LIMIT ' . $limit;
            }

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

            json_response(['status' => 'success', 'vendors' => $vendors]);
        }

        elseif ($action === 'get_approved_vendors') {
            // Get only approved vendors for contract creation dropdown
            $stmt = $conn->prepare('SELECT id, vendor_name FROM vendor_portal_registration WHERE status = :status ORDER BY vendor_name ASC');
            $stmt->execute([':status' => 'Approved']);
            $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status' => 'success', 'vendors' => $vendors]);
        }

        elseif ($action === 'get_vendor_details') {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) {
                json_response(['status' => 'error', 'message' => 'ID required'], 400);
            }

            $stmt = $conn->prepare('SELECT * FROM vendor_portal_registration WHERE id = ?');
            $stmt->execute([$id]);
            $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$vendor) {
                json_response(['status' => 'error', 'message' => 'Vendor not found'], 404);
            }

            json_response(['status' => 'success', 'vendor' => $vendor]);
        }

        // Validation endpoints
        elseif ($action === 'get_validations') {
            $vendorId = isset($_GET['vendor_id']) ? (int)$_GET['vendor_id'] : null;
            $status = isset($_GET['status']) ? trim($_GET['status']) : null;

            $where = [];
            $params = [];

            if ($vendorId) {
                $where[] = 'v.vendor_id = :vendor_id';
                $params[':vendor_id'] = $vendorId;
            }

            if ($status) {
                $where[] = 'v.validation_status = :status';
                $params[':status'] = $status;
            }

            $sql = 'SELECT v.*, vp.vendor_name FROM vendor_validation_checklist v 
                    LEFT JOIN vendor_portal_registration vp ON v.vendor_id = vp.id';
            if ($where) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= ' ORDER BY v.created_at DESC';

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $validations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            json_response(['status' => 'success', 'validations' => $validations]);
        }

        elseif ($action === 'get_validation') {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) {
                json_response(['status' => 'error', 'message' => 'ID required'], 400);
            }

            $stmt = $conn->prepare('SELECT * FROM vendor_validation_checklist WHERE id = ?');
            $stmt->execute([$id]);
            $validation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$validation) {
                json_response(['status' => 'error', 'message' => 'Validation not found'], 404);
            }

            json_response(['status' => 'success', 'validation' => $validation]);
        }

        // Verification endpoints
        elseif ($action === 'get_verifications') {
            $vendorId = isset($_GET['vendor_id']) ? (int)$_GET['vendor_id'] : null;
            $type = isset($_GET['type']) ? trim($_GET['type']) : null;

            $where = [];
            $params = [];

            if ($vendorId) {
                $where[] = 'v.vendor_id = :vendor_id';
                $params[':vendor_id'] = $vendorId;
            }

            if ($type) {
                $where[] = 'v.verification_type = :type';
                $params[':type'] = $type;
            }

            $sql = 'SELECT v.*, vp.vendor_name FROM vendor_verification v 
                    LEFT JOIN vendor_portal_registration vp ON v.vendor_id = vp.id';
            if ($where) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= ' ORDER BY v.created_at DESC';

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $verifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            json_response(['status' => 'success', 'verifications' => $verifications]);
        }

        elseif ($action === 'get_verification') {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) {
                json_response(['status' => 'error', 'message' => 'ID required'], 400);
            }

            $stmt = $conn->prepare('SELECT * FROM vendor_verification WHERE id = ?');
            $stmt->execute([$id]);
            $verification = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$verification) {
                json_response(['status' => 'error', 'message' => 'Verification not found'], 404);
            }

            json_response(['status' => 'success', 'verification' => $verification]);
        }

        // Requirements endpoints
        elseif ($action === 'get_requirements') {
            $vendorId = isset($_GET['vendor_id']) ? (int)$_GET['vendor_id'] : null;
            $type = isset($_GET['type']) ? trim($_GET['type']) : null;

            $where = [];
            $params = [];

            if ($vendorId) {
                $where[] = 'r.vendor_id = :vendor_id';
                $params[':vendor_id'] = $vendorId;
            }

            if ($type) {
                $where[] = 'r.requirement_type = :type';
                $params[':type'] = $type;
            }

            $sql = 'SELECT r.*, vp.vendor_name FROM vendor_requirements r 
                    LEFT JOIN vendor_portal_registration vp ON r.vendor_id = vp.id';
            if ($where) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= ' ORDER BY r.created_at DESC';

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            json_response(['status' => 'success', 'requirements' => $requirements]);
        }

        elseif ($action === 'get_requirement') {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) {
                json_response(['status' => 'error', 'message' => 'ID required'], 400);
            }

            $stmt = $conn->prepare('SELECT * FROM vendor_requirements WHERE id = ?');
            $stmt->execute([$id]);
            $requirement = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$requirement) {
                json_response(['status' => 'error', 'message' => 'Requirement not found'], 404);
            }

            json_response(['status' => 'success', 'requirement' => $requirement]);
        }
    }

    // ===== POST REQUESTS =====
    elseif ($method === 'POST') {
        $action = $input['action'] ?? null;

        // Create/Update Vendor
        if (!$action || $action === 'save_vendor') {
            $vendorId = $input['id'] ?? null;
            $vendorName = trim($input['vendor_name'] ?? '');
            $companyName = trim($input['company_name'] ?? '');
            $contactPerson = trim($input['contact_person'] ?? '');
            $email = trim($input['email'] ?? '');
            $phone = trim($input['phone'] ?? '');
            $address = $input['address'] ?? '';
            $city = $input['city'] ?? '';
            $stateProvince = $input['state_province'] ?? '';
            $country = $input['country'] ?? '';
            $postalCode = $input['postal_code'] ?? '';
            $taxId = $input['tax_id'] ?? '';
            $registrationNumber = $input['registration_number'] ?? '';
            $businessType = $input['business_type'] ?? '';
            $annualRevenue = $input['annual_revenue'] ?? null;
            $employeesCount = $input['employees_count'] ?? null;
            $websiteUrl = $input['website_url'] ?? '';
            $yearsInBusiness = $input['years_in_business'] ?? null;
            $status = $input['status'] ?? 'Draft';

            if (!$vendorName || !$companyName || !$contactPerson || !$email || !$phone) {
                json_response(['status' => 'error', 'message' => 'Missing required fields'], 400);
            }

            try {
                if ($vendorId) {
                    $stmt = $conn->prepare('UPDATE vendor_portal_registration SET 
                        vendor_name = ?, company_name = ?, contact_person = ?, email = ?, phone = ?,
                        address = ?, city = ?, state_province = ?, country = ?, postal_code = ?,
                        tax_id = ?, registration_number = ?, business_type = ?, annual_revenue = ?,
                        employees_count = ?, website_url = ?, years_in_business = ?, status = ?
                        WHERE id = ?');
                    $stmt->execute([
                        $vendorName, $companyName, $contactPerson, $email, $phone,
                        $address, $city, $stateProvince, $country, $postalCode,
                        $taxId, $registrationNumber, $businessType, $annualRevenue,
                        $employeesCount, $websiteUrl, $yearsInBusiness, $status, $vendorId
                    ]);
                    $message = 'Vendor updated successfully';
                    $id = $vendorId;
                } else {
                    $stmt = $conn->prepare('INSERT INTO vendor_portal_registration 
                        (vendor_name, company_name, contact_person, email, phone, address, city,
                         state_province, country, postal_code, tax_id, registration_number, business_type,
                         annual_revenue, employees_count, website_url, years_in_business, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                    $stmt->execute([
                        $vendorName, $companyName, $contactPerson, $email, $phone, $address, $city,
                        $stateProvince, $country, $postalCode, $taxId, $registrationNumber, $businessType,
                        $annualRevenue, $employeesCount, $websiteUrl, $yearsInBusiness, $status
                    ]);
                    $id = $conn->lastInsertId();
                    $message = 'Vendor registered successfully';

                    // Create empty validation record for new vendor
                    $stmt2 = $conn->prepare('INSERT INTO vendor_validation_checklist (vendor_id) VALUES (?)');
                    $stmt2->execute([$id]);
                }

                $stmt3 = $conn->prepare('SELECT * FROM vendor_portal_registration WHERE id = ?');
                $stmt3->execute([$id]);
                $vendor = $stmt3->fetch(PDO::FETCH_ASSOC);

                json_response(['status' => 'success', 'message' => $message, 'data' => $vendor], 201);
            } catch (PDOException $e) {
                if ((int)$e->getCode() === 23000) {
                    json_response(['status' => 'error', 'message' => 'Email already exists'], 409);
                }
                throw $e;
            }
        }

        // Save Validation
        elseif ($action === 'save_validation') {
            $vendorId = (int)($input['vendor_id'] ?? 0);
            $businessLicense = $input['business_license_verified'] ?? 0;
            $taxCompliance = $input['tax_compliance_verified'] ?? 0;
            $financialStatements = $input['financial_statements_verified'] ?? 0;
            $referencesChecked = $input['references_checked'] ?? 0;
            $insuranceDocuments = $input['insurance_documents_verified'] ?? 0;
            $complianceDocuments = $input['compliance_documents_verified'] ?? 0;
            $backgroundCheck = $input['background_check_done'] ?? 0;
            $validationStatus = $input['validation_status'] ?? 'Pending';
            $validationNotes = $input['validation_notes'] ?? '';

            if (!$vendorId) {
                json_response(['status' => 'error', 'message' => 'Vendor ID required'], 400);
            }

            // Check if validation record exists
            $stmt = $conn->prepare('SELECT id FROM vendor_validation_checklist WHERE vendor_id = ?');
            $stmt->execute([$vendorId]);
            $exists = $stmt->fetch();

            if ($exists) {
                $stmt = $conn->prepare('UPDATE vendor_validation_checklist SET 
                    business_license_verified = ?, tax_compliance_verified = ?,
                    financial_statements_verified = ?, references_checked = ?,
                    insurance_documents_verified = ?, compliance_documents_verified = ?,
                    background_check_done = ?, validation_status = ?, validation_notes = ?,
                    validation_date = NOW(), validated_by = ?
                    WHERE vendor_id = ?');
                $stmt->execute([
                    $businessLicense, $taxCompliance, $financialStatements, $referencesChecked,
                    $insuranceDocuments, $complianceDocuments, $backgroundCheck, $validationStatus,
                    $validationNotes, $_SESSION['name'] ?? 'Admin', $vendorId
                ]);
            } else {
                $stmt = $conn->prepare('INSERT INTO vendor_validation_checklist 
                    (vendor_id, business_license_verified, tax_compliance_verified,
                     financial_statements_verified, references_checked, insurance_documents_verified,
                     compliance_documents_verified, background_check_done, validation_status,
                     validation_notes, validation_date, validated_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)');
                $stmt->execute([
                    $vendorId, $businessLicense, $taxCompliance, $financialStatements,
                    $referencesChecked, $insuranceDocuments, $complianceDocuments, $backgroundCheck,
                    $validationStatus, $validationNotes, $_SESSION['name'] ?? 'Admin'
                ]);
            }

            json_response(['status' => 'success', 'message' => 'Validation saved']);
        }

        // Save Verification
        elseif ($action === 'save_verification') {
            $vendorId = (int)($input['vendor_id'] ?? 0);
            $verificationType = $input['verification_type'] ?? '';
            $verificationStatus = $input['verification_status'] ?? 'Pending';
            $verificationNotes = $input['verification_notes'] ?? '';

            if (!$vendorId || !$verificationType) {
                json_response(['status' => 'error', 'message' => 'Missing required fields'], 400);
            }

            $stmt = $conn->prepare('INSERT INTO vendor_verification 
                (vendor_id, verification_type, verification_status, verification_notes, 
                 verification_date, verified_by)
                VALUES (?, ?, ?, ?, NOW(), ?)');
            $stmt->execute([
                $vendorId, $verificationType, $verificationStatus, $verificationNotes,
                $_SESSION['name'] ?? 'Admin'
            ]);

            json_response(['status' => 'success', 'message' => 'Verification saved']);
        }

        // Save Requirement
        elseif ($action === 'save_requirement') {
            $vendorId = (int)($input['vendor_id'] ?? 0);
            $requirementType = $input['requirement_type'] ?? '';
            $requirementName = $input['requirement_name'] ?? '';
            $requirementDescription = $input['requirement_description'] ?? '';
            $isMandatory = $input['is_mandatory'] ?? 1;
            $requirementStatus = $input['requirement_status'] ?? 'Not Started';
            $expiresDate = $input['expires_date'] ?? null;

            if (!$vendorId || !$requirementType || !$requirementName) {
                json_response(['status' => 'error', 'message' => 'Missing required fields'], 400);
            }

            $stmt = $conn->prepare('INSERT INTO vendor_requirements 
                (vendor_id, requirement_type, requirement_name, requirement_description,
                 is_mandatory, requirement_status, expires_date)
                VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $vendorId, $requirementType, $requirementName, $requirementDescription,
                $isMandatory, $requirementStatus, $expiresDate
            ]);

            json_response(['status' => 'success', 'message' => 'Requirement saved']);
        }
    }

    // ===== PUT REQUESTS =====
    elseif ($method === 'PUT') {
        parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
        $id = $qs['id'] ?? null;
        
        if (!$id) {
            json_response(['status' => 'error', 'message' => 'ID required'], 400);
        }

        // Update Vendor
        $stmt = $conn->prepare('UPDATE vendor_portal_registration SET 
            vendor_name = ?, company_name = ?, contact_person = ?, email = ?, phone = ?,
            address = ?, city = ?, state_province = ?, country = ?, postal_code = ?,
            tax_id = ?, registration_number = ?, business_type = ?, annual_revenue = ?,
            employees_count = ?, website_url = ?, years_in_business = ?
            WHERE id = ?');
        
        $stmt->execute([
            $input['vendor_name'] ?? '',
            $input['company_name'] ?? '',
            $input['contact_person'] ?? '',
            $input['email'] ?? '',
            $input['phone'] ?? '',
            $input['address'] ?? '',
            $input['city'] ?? '',
            $input['state_province'] ?? '',
            $input['country'] ?? '',
            $input['postal_code'] ?? '',
            $input['tax_id'] ?? '',
            $input['registration_number'] ?? '',
            $input['business_type'] ?? '',
            $input['annual_revenue'] ?? null,
            $input['employees_count'] ?? null,
            $input['website_url'] ?? '',
            $input['years_in_business'] ?? null,
            $id
        ]);

        $stmt2 = $conn->prepare('SELECT * FROM vendor_portal_registration WHERE id = ?');
        $stmt2->execute([$id]);
        $vendor = $stmt2->fetch(PDO::FETCH_ASSOC);

        json_response(['status' => 'success', 'message' => 'Vendor updated', 'data' => $vendor]);
    }

    // ===== DELETE REQUESTS =====
    elseif ($method === 'DELETE') {
        $action = $_GET['action'] ?? null;
        $id = $_GET['id'] ?? null;

        if (!$id) {
            json_response(['status' => 'error', 'message' => 'ID required'], 400);
        }

        if ($action === 'delete_verification') {
            $stmt = $conn->prepare('DELETE FROM vendor_verification WHERE id = ?');
            $stmt->execute([$id]);
            json_response(['status' => 'success', 'message' => 'Verification deleted']);
        }

        elseif ($action === 'delete_requirement') {
            $stmt = $conn->prepare('DELETE FROM vendor_requirements WHERE id = ?');
            $stmt->execute([$id]);
            json_response(['status' => 'success', 'message' => 'Requirement deleted']);
        }

        else {
            // Delete vendor
            $stmt = $conn->prepare('DELETE FROM vendor_portal_registration WHERE id = ?');
            $stmt->execute([$id]);
            json_response(['status' => 'success', 'message' => 'Vendor deleted']);
        }
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

?>
