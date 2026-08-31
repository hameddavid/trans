-- ============================================================
-- Registration records for test students 11223344AA and 11223344AB
-- 4 years (2021/2022 – 2024/2025), 2 semesters each = 8 semesters
-- ~6 courses per semester = ~48 records per student
-- ============================================================

-- ========================
-- STUDENT 1: 11223344AA
-- ========================

-- 100 Level — Semester 1 (2021/2022)
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AA', 1, '2021/2022', 'GST 101', 'C', 45.00, 25.00, 70, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AA', 1, '2021/2022', 'GST 103', 'C', 40.00, 22.00, 62, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AA', 1, '2021/2022', 'GST 105', 'C', 38.00, 20.00, 58, 'C', 'Good', 'N', '20050901', 0),
('11223344AA', 1, '2021/2022', 'ECO 101', 'C', 42.00, 23.00, 65, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AA', 1, '2021/2022', 'ECO 103', 'C', 35.00, 18.00, 53, 'C', 'Good', 'N', '20050901', 0),
('11223344AA', 1, '2021/2022', 'CMS 101', 'C', 44.00, 24.00, 68, 'B', 'Very Good', 'N', '20050901', 0);

-- 100 Level — Semester 2 (2021/2022)
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AA', 2, '2021/2022', 'GST 102', 'C', 46.00, 26.00, 72, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AA', 2, '2021/2022', 'GST 104', 'C', 37.00, 19.00, 56, 'C', 'Good', 'N', '20050901', 0),
('11223344AA', 2, '2021/2022', 'GST 106', 'C', 40.00, 22.00, 62, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AA', 2, '2021/2022', 'ECO 102', 'C', 43.00, 24.00, 67, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AA', 2, '2021/2022', 'ECO 104', 'C', 30.00, 15.00, 45, 'D', 'Fair', 'N', '20050901', 0),
('11223344AA', 2, '2021/2022', 'ECO 106', 'C', 48.00, 27.00, 75, 'A', 'Excellent', 'N', '20050901', 0);

-- 200 Level — Semester 1 (2022/2023)
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AA', 1, '2022/2023', 'ECO 201', 'C', 44.00, 24.00, 68, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AA', 1, '2022/2023', 'ECO 203', 'C', 40.00, 22.00, 62, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AA', 1, '2022/2023', 'ECO 205', 'C', 35.00, 18.00, 53, 'C', 'Good', 'N', '20050901', 0),
('11223344AA', 1, '2022/2023', 'ECO 207', 'C', 48.00, 27.00, 75, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AA', 1, '2022/2023', 'ECO 209', 'C', 30.00, 15.00, 45, 'D', 'Fair', 'N', '20050901', 0),
('11223344AA', 1, '2022/2023', 'SOC 201', 'C', 42.00, 23.00, 65, 'B', 'Very Good', 'N', '20050901', 0);

-- 200 Level — Semester 2 (2022/2023)
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AA', 2, '2022/2023', 'ECO 202', 'C', 46.00, 25.00, 71, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AA', 2, '2022/2023', 'ECO 204', 'C', 38.00, 20.00, 58, 'C', 'Good', 'N', '20050901', 0),
('11223344AA', 2, '2022/2023', 'ECO 206', 'C', 42.00, 23.00, 65, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AA', 2, '2022/2023', 'ECO 208', 'C', 50.00, 28.00, 78, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AA', 2, '2022/2023', 'ECO 210', 'C', 33.00, 17.00, 50, 'C', 'Good', 'N', '20050901', 0),
('11223344AA', 2, '2022/2023', 'SOC 202', 'C', 36.00, 19.00, 55, 'C', 'Good', 'N', '20050901', 0);

-- 300 Level — Semester 1 (2023/2024)
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AA', 1, '2023/2024', 'ECO 301', 'C', 47.00, 26.00, 73, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AA', 1, '2023/2024', 'ECO 303', 'C', 41.00, 22.00, 63, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AA', 1, '2023/2024', 'ECO 305', 'C', 36.00, 19.00, 55, 'C', 'Good', 'N', '20050901', 0),
('11223344AA', 1, '2023/2024', 'ECO 307', 'C', 50.00, 28.00, 78, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AA', 1, '2023/2024', 'ECO 309', 'C', 44.00, 24.00, 68, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AA', 1, '2023/2024', 'ECO 311', 'C', 39.00, 21.00, 60, 'B', 'Very Good', 'N', '20050901', 0);

-- 300 Level — Semester 2 (2023/2024)
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AA', 2, '2023/2024', 'ECO 302', 'C', 45.00, 25.00, 70, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AA', 2, '2023/2024', 'ECO 304', 'C', 40.00, 22.00, 62, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AA', 2, '2023/2024', 'ECO 306', 'C', 28.00, 14.00, 42, 'D', 'Fair', 'N', '20050901', 0),
('11223344AA', 2, '2023/2024', 'ECO 308', 'C', 48.00, 27.00, 75, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AA', 2, '2023/2024', 'ECO 310', 'C', 37.00, 20.00, 57, 'C', 'Good', 'N', '20050901', 0),
('11223344AA', 2, '2023/2024', 'ECO 312', 'C', 43.00, 23.00, 66, 'B', 'Very Good', 'N', '20050901', 0);

-- 400 Level — Semester 1 (2024/2025)
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AA', 1, '2024/2025', 'ECO 401', 'C', 50.00, 28.00, 78, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AA', 1, '2024/2025', 'ECO 403', 'C', 46.00, 25.00, 71, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AA', 1, '2024/2025', 'ECO 405', 'C', 42.00, 23.00, 65, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AA', 1, '2024/2025', 'ECO 407', 'C', 38.00, 20.00, 58, 'C', 'Good', 'N', '20050901', 0),
('11223344AA', 1, '2024/2025', 'ECO 409', 'C', 44.00, 24.00, 68, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AA', 1, '2024/2025', 'ECO 411', 'C', 47.00, 26.00, 73, 'A', 'Excellent', 'N', '20050901', 0);

-- 400 Level — Semester 2 (2024/2025)
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AA', 2, '2024/2025', 'ECO 402', 'C', 49.00, 27.00, 76, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AA', 2, '2024/2025', 'ECO 404', 'C', 43.00, 24.00, 67, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AA', 2, '2024/2025', 'ECO 406', 'C', 35.00, 18.00, 53, 'C', 'Good', 'N', '20050901', 0),
('11223344AA', 2, '2024/2025', 'ECO 408', 'C', 47.00, 26.00, 73, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AA', 2, '2024/2025', 'ECO 410', 'C', 40.00, 22.00, 62, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AA', 2, '2024/2025', 'ECO 499', 'C', 52.00, 30.00, 82, 'A', 'Excellent', 'N', '20050901', 0);


-- ========================
-- STUDENT 2: 11223344AB
-- (Slightly different grades — includes a failed course retaken and passed)
-- ========================

-- 100 Level — Semester 1 (2021/2022)
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AB', 1, '2021/2022', 'GST 101', 'C', 40.00, 22.00, 62, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AB', 1, '2021/2022', 'GST 103', 'C', 35.00, 18.00, 53, 'C', 'Good', 'N', '20050901', 0),
('11223344AB', 1, '2021/2022', 'GST 105', 'C', 25.00, 12.00, 37, 'F', 'Poor', 'N', '20050901', 0),
('11223344AB', 1, '2021/2022', 'ECO 101', 'C', 38.00, 20.00, 58, 'C', 'Good', 'N', '20050901', 0),
('11223344AB', 1, '2021/2022', 'ECO 103', 'C', 30.00, 15.00, 45, 'D', 'Fair', 'N', '20050901', 0),
('11223344AB', 1, '2021/2022', 'CMS 101', 'C', 42.00, 23.00, 65, 'B', 'Very Good', 'N', '20050901', 0);

-- 100 Level — Semester 2 (2021/2022)
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AB', 2, '2021/2022', 'GST 102', 'C', 43.00, 24.00, 67, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AB', 2, '2021/2022', 'GST 104', 'C', 32.00, 16.00, 48, 'D', 'Fair', 'N', '20050901', 0),
('11223344AB', 2, '2021/2022', 'GST 106', 'C', 37.00, 19.00, 56, 'C', 'Good', 'N', '20050901', 0),
('11223344AB', 2, '2021/2022', 'ECO 102', 'C', 40.00, 22.00, 62, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AB', 2, '2021/2022', 'ECO 104', 'C', 20.00, 10.00, 30, 'F', 'Poor', 'N', '20050901', 0),
('11223344AB', 2, '2021/2022', 'ECO 106', 'C', 44.00, 24.00, 68, 'B', 'Very Good', 'N', '20050901', 0);

-- 200 Level — Semester 1 (2022/2023)
-- Includes retake of GST 105 (failed in 100L sem1) — now passed
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AB', 1, '2022/2023', 'GST 105', 'C', 38.00, 20.00, 58, 'C', 'Good', 'N', '20050901', 0),
('11223344AB', 1, '2022/2023', 'ECO 201', 'C', 41.00, 22.00, 63, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AB', 1, '2022/2023', 'ECO 203', 'C', 36.00, 19.00, 55, 'C', 'Good', 'N', '20050901', 0),
('11223344AB', 1, '2022/2023', 'ECO 205', 'C', 45.00, 25.00, 70, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AB', 1, '2022/2023', 'ECO 207', 'C', 32.00, 16.00, 48, 'D', 'Fair', 'N', '20050901', 0),
('11223344AB', 1, '2022/2023', 'SOC 201', 'C', 39.00, 21.00, 60, 'B', 'Very Good', 'N', '20050901', 0);

-- 200 Level — Semester 2 (2022/2023)
-- Includes retake of ECO 104 (failed in 100L sem2) — now passed
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AB', 2, '2022/2023', 'ECO 104', 'C', 35.00, 18.00, 53, 'C', 'Good', 'N', '20050901', 0),
('11223344AB', 2, '2022/2023', 'ECO 202', 'C', 43.00, 24.00, 67, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AB', 2, '2022/2023', 'ECO 204', 'C', 37.00, 19.00, 56, 'C', 'Good', 'N', '20050901', 0),
('11223344AB', 2, '2022/2023', 'ECO 206', 'C', 48.00, 27.00, 75, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AB', 2, '2022/2023', 'ECO 208', 'C', 40.00, 22.00, 62, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AB', 2, '2022/2023', 'SOC 202', 'C', 34.00, 17.00, 51, 'C', 'Good', 'N', '20050901', 0);

-- 300 Level — Semester 1 (2023/2024)
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AB', 1, '2023/2024', 'ECO 301', 'C', 44.00, 24.00, 68, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AB', 1, '2023/2024', 'ECO 303', 'C', 38.00, 20.00, 58, 'C', 'Good', 'N', '20050901', 0),
('11223344AB', 1, '2023/2024', 'ECO 305', 'C', 46.00, 25.00, 71, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AB', 1, '2023/2024', 'ECO 307', 'C', 42.00, 23.00, 65, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AB', 1, '2023/2024', 'ECO 309', 'C', 33.00, 17.00, 50, 'C', 'Good', 'N', '20050901', 0),
('11223344AB', 1, '2023/2024', 'ECO 311', 'C', 40.00, 22.00, 62, 'B', 'Very Good', 'N', '20050901', 0);

-- 300 Level — Semester 2 (2023/2024)
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AB', 2, '2023/2024', 'ECO 302', 'C', 47.00, 26.00, 73, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AB', 2, '2023/2024', 'ECO 304', 'C', 35.00, 18.00, 53, 'C', 'Good', 'N', '20050901', 0),
('11223344AB', 2, '2023/2024', 'ECO 306', 'C', 41.00, 22.00, 63, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AB', 2, '2023/2024', 'ECO 308', 'C', 50.00, 28.00, 78, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AB', 2, '2023/2024', 'ECO 310', 'C', 36.00, 19.00, 55, 'C', 'Good', 'N', '20050901', 0),
('11223344AB', 2, '2023/2024', 'ECO 312', 'C', 39.00, 21.00, 60, 'B', 'Very Good', 'N', '20050901', 0);

-- 400 Level — Semester 1 (2024/2025)
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AB', 1, '2024/2025', 'ECO 401', 'C', 45.00, 25.00, 70, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AB', 1, '2024/2025', 'ECO 403', 'C', 40.00, 22.00, 62, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AB', 1, '2024/2025', 'ECO 405', 'C', 37.00, 19.00, 56, 'C', 'Good', 'N', '20050901', 0),
('11223344AB', 1, '2024/2025', 'ECO 407', 'C', 43.00, 24.00, 67, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AB', 1, '2024/2025', 'ECO 409', 'C', 48.00, 27.00, 75, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AB', 1, '2024/2025', 'ECO 411', 'C', 34.00, 17.00, 51, 'C', 'Good', 'N', '20050901', 0);

-- 400 Level — Semester 2 (2024/2025)
INSERT INTO registrations (matric_number, semester, session_id, course_code, status, score, ca, total_score, grade, remarks, deleted, unit_id, flag_waver)
VALUES
('11223344AB', 2, '2024/2025', 'ECO 402', 'C', 46.00, 25.00, 71, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AB', 2, '2024/2025', 'ECO 404', 'C', 41.00, 22.00, 63, 'B', 'Very Good', 'N', '20050901', 0),
('11223344AB', 2, '2024/2025', 'ECO 406', 'C', 38.00, 20.00, 58, 'C', 'Good', 'N', '20050901', 0),
('11223344AB', 2, '2024/2025', 'ECO 408', 'C', 50.00, 28.00, 78, 'A', 'Excellent', 'N', '20050901', 0),
('11223344AB', 2, '2024/2025', 'ECO 410', 'C', 35.00, 18.00, 53, 'C', 'Good', 'N', '20050901', 0),
('11223344AB', 2, '2024/2025', 'ECO 499', 'C', 44.00, 24.00, 68, 'B', 'Very Good', 'N', '20050901', 0);
