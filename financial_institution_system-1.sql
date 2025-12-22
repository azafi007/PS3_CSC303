-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2025 at 09:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `financial institution system`
--

-- --------------------------------------------------------

--
-- Table structure for table `auditor`
--

CREATE TABLE `auditor` (
  `StaffID` int(8) NOT NULL,
  `last review date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auditor`
--

INSERT INTO `auditor` (`StaffID`, `last review date`) VALUES
(103, '2025-08-11'),
(104, '2025-12-08');

-- --------------------------------------------------------

--
-- Table structure for table `audit_report`
--

CREATE TABLE `audit_report` (
  `ReportID` int(8) NOT NULL,
  `StaffID` int(8) DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `FindingsSummary` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_report`
--

INSERT INTO `audit_report` (`ReportID`, `StaffID`, `Date`, `FindingsSummary`) VALUES
(1, 103, '2025-12-19', 'Verified tech sector transactions; found one high-risk alert for review.'),
(2, 103, '2025-12-19', 'Verified tech sector transactions; found one high-risk alert for review.');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `ParticipantID` int(8) NOT NULL,
  `registrationNumber` varchar(10) DEFAULT NULL,
  `totalShare` int(10) DEFAULT NULL,
  `currentPrice` decimal(10,2) DEFAULT NULL,
  `registrationDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`ParticipantID`, `registrationNumber`, `totalShare`, `currentPrice`, `registrationDate`) VALUES
(503, 'REG-12345', 1000000, 310.20, '2020-05-15');

-- --------------------------------------------------------

--
-- Table structure for table `financial_analyst`
--

CREATE TABLE `financial_analyst` (
  `StaffID` int(8) NOT NULL,
  `specialized area` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_analyst`
--

INSERT INTO `financial_analyst` (`StaffID`, `specialized area`) VALUES
(102, 'Technology and Energy');

-- --------------------------------------------------------

--
-- Table structure for table `fraud_alert`
--

CREATE TABLE `fraud_alert` (
  `AlertID` int(8) NOT NULL,
  `riskScore` int(11) DEFAULT NULL,
  `detectionDate` date DEFAULT NULL,
  `TransactionID` int(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fraud_alert`
--

INSERT INTO `fraud_alert` (`AlertID`, `riskScore`, `detectionDate`, `TransactionID`) VALUES
(111, 92, '2025-12-02', 9001);

-- --------------------------------------------------------

--
-- Table structure for table `institution`
--

CREATE TABLE `institution` (
  `ParticipantID` int(8) NOT NULL,
  `licenseNo` varchar(10) DEFAULT NULL,
  `totalShare` int(10) DEFAULT NULL,
  `currentPrice` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `institution`
--

INSERT INTO `institution` (`ParticipantID`, `licenseNo`, `totalShare`, `currentPrice`) VALUES
(501, 'LIC-998877', 500000, 1250.50),
(504, 'LIC-112233', 850000, 2100.75);

-- --------------------------------------------------------

--
-- Table structure for table `investor`
--

CREATE TABLE `investor` (
  `ParticipantID` int(8) NOT NULL,
  `totalShare` int(11) DEFAULT NULL,
  `currentPrice` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `investor`
--

INSERT INTO `investor` (`ParticipantID`, `totalShare`, `currentPrice`) VALUES
(502, 1200, 45.75);

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `StaffID` int(8) NOT NULL,
  `activity` varchar(30) DEFAULT NULL,
  `loginTime` datetime NOT NULL,
  `tradeApproval` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manager`
--

CREATE TABLE `manager` (
  `StaffID` int(11) NOT NULL,
  `reportApproval` varchar(20) DEFAULT NULL,
  `approvalStatus` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `manager`
--

INSERT INTO `manager` (`StaffID`, `reportApproval`, `approvalStatus`) VALUES
(101, 'Standard Monthly Rev', 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `prediction`
--

CREATE TABLE `prediction` (
  `StockID` int(8) NOT NULL,
  `predictionDateTime` datetime NOT NULL,
  `predictedPrice` decimal(10,2) DEFAULT NULL,
  `targetDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prediction`
--

INSERT INTO `prediction` (`StockID`, `predictionDateTime`, `predictedPrice`, `targetDate`) VALUES
(1, '2025-12-11 12:00:00', 325.50, '2026-01-31'),
(1, '2025-12-19 12:00:00', 325.50, '2026-01-31');

-- --------------------------------------------------------

--
-- Table structure for table `price_history`
--

CREATE TABLE `price_history` (
  `StockID` int(8) NOT NULL,
  `recordingTime` datetime NOT NULL,
  `openingPrice` decimal(8,2) DEFAULT NULL,
  `closingPrice` decimal(8,2) DEFAULT NULL,
  `high` decimal(8,2) DEFAULT NULL,
  `low` decimal(8,2) DEFAULT NULL,
  `volume` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `price_history`
--

INSERT INTO `price_history` (`StockID`, `recordingTime`, `openingPrice`, `closingPrice`, `high`, `low`, `volume`) VALUES
(1, '2025-12-18 09:00:00', 305.00, 308.50, 310.00, 304.00, 45000),
(1, '2025-12-19 09:00:00', 308.50, 310.20, 312.00, 307.50, 52000);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `StaffID` int(8) NOT NULL,
  `userName` varchar(20) DEFAULT NULL,
  `name` varchar(20) DEFAULT NULL,
  `designation` varchar(20) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`StaffID`, `userName`, `name`, `designation`, `contact`) VALUES
(101, 'arafat_me', 'arafat', 'Manager', '123444'),
(102, 'smith_12', 'Alice Smith', 'Financial Analyst', '555-0102'),
(103, 'rbrown_aud', 'Robert Brown', 'Lead Audit', '555-0103'),
(104, 'azafi_12', 'Azafi Sifat', ' junior Audit', '555-0104');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `StockID` int(8) NOT NULL,
  `ParticipantID` int(8) DEFAULT NULL,
  `totalShare` int(11) DEFAULT NULL,
  `currentPrice` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`StockID`, `ParticipantID`, `totalShare`, `currentPrice`) VALUES
(1, 503, 1000000, 310.20);

-- --------------------------------------------------------

--
-- Table structure for table `stock_transaction`
--

CREATE TABLE `stock_transaction` (
  `TransactionID` int(8) NOT NULL,
  `timeStamp` datetime DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `StockID` int(8) DEFAULT NULL,
  `ParticipantID` int(8) DEFAULT NULL,
  `StaffID` int(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_transaction`
--

INSERT INTO `stock_transaction` (`TransactionID`, `timeStamp`, `amount`, `StockID`, `ParticipantID`, `StaffID`) VALUES
(9001, '2025-12-19 14:30:00', 15510.00, 1, 502, 101);

-- --------------------------------------------------------

--
-- Table structure for table `trades`
--

CREATE TABLE `trades` (
  `TradeID` int(8) NOT NULL,
  `tradeAmount` decimal(15,2) DEFAULT NULL,
  `tradeDate` date DEFAULT NULL,
  `BuyerID` int(8) DEFAULT NULL,
  `SellerID` int(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_participant`
--

CREATE TABLE `transaction_participant` (
  `ParticipantID` int(8) NOT NULL,
  `name` varchar(20) DEFAULT NULL,
  `accountType` varchar(20) DEFAULT NULL,
  `email` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_participant`
--

INSERT INTO `transaction_participant` (`ParticipantID`, `name`, `accountType`, `email`) VALUES
(501, 'Global Investments L', 'Corporate', 'contact@globalinv.co'),
(502, 'Sarah Jenkins', 'Individual', 'sarah.j@email.com'),
(503, 'TechCorp Solutions', 'Corporate', 'info@techcorp.com'),
(504, 'Future Growth Fund', 'Institutional', 'ops@futuregrowth.org');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auditor`
--
ALTER TABLE `auditor`
  ADD PRIMARY KEY (`StaffID`);

--
-- Indexes for table `audit_report`
--
ALTER TABLE `audit_report`
  ADD PRIMARY KEY (`ReportID`),
  ADD KEY `StaffID` (`StaffID`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`ParticipantID`);

--
-- Indexes for table `financial_analyst`
--
ALTER TABLE `financial_analyst`
  ADD PRIMARY KEY (`StaffID`);

--
-- Indexes for table `fraud_alert`
--
ALTER TABLE `fraud_alert`
  ADD PRIMARY KEY (`AlertID`),
  ADD KEY `TransactionID` (`TransactionID`);

--
-- Indexes for table `institution`
--
ALTER TABLE `institution`
  ADD PRIMARY KEY (`ParticipantID`);

--
-- Indexes for table `investor`
--
ALTER TABLE `investor`
  ADD PRIMARY KEY (`ParticipantID`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`StaffID`,`loginTime`);

--
-- Indexes for table `manager`
--
ALTER TABLE `manager`
  ADD PRIMARY KEY (`StaffID`);

--
-- Indexes for table `prediction`
--
ALTER TABLE `prediction`
  ADD PRIMARY KEY (`StockID`,`predictionDateTime`);

--
-- Indexes for table `price_history`
--
ALTER TABLE `price_history`
  ADD PRIMARY KEY (`StockID`,`recordingTime`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`StaffID`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`StockID`),
  ADD KEY `ParticipantID` (`ParticipantID`);

--
-- Indexes for table `stock_transaction`
--
ALTER TABLE `stock_transaction`
  ADD PRIMARY KEY (`TransactionID`),
  ADD KEY `StockID` (`StockID`),
  ADD KEY `ParticipantID` (`ParticipantID`),
  ADD KEY `StaffID` (`StaffID`);

--
-- Indexes for table `trades`
--
ALTER TABLE `trades`
  ADD PRIMARY KEY (`TradeID`),
  ADD KEY `BuyerID` (`BuyerID`),
  ADD KEY `SellerID` (`SellerID`);

--
-- Indexes for table `transaction_participant`
--
ALTER TABLE `transaction_participant`
  ADD PRIMARY KEY (`ParticipantID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_report`
--
ALTER TABLE `audit_report`
  MODIFY `ReportID` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fraud_alert`
--
ALTER TABLE `fraud_alert`
  MODIFY `AlertID` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `stock_transaction`
--
ALTER TABLE `stock_transaction`
  MODIFY `TransactionID` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9002;

--
-- AUTO_INCREMENT for table `trades`
--
ALTER TABLE `trades`
  MODIFY `TradeID` int(8) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auditor`
--
ALTER TABLE `auditor`
  ADD CONSTRAINT `auditor_ibfk_1` FOREIGN KEY (`StaffID`) REFERENCES `staff` (`StaffID`) ON DELETE CASCADE;

--
-- Constraints for table `audit_report`
--
ALTER TABLE `audit_report`
  ADD CONSTRAINT `audit_report_ibfk_1` FOREIGN KEY (`StaffID`) REFERENCES `auditor` (`StaffID`) ON DELETE SET NULL;

--
-- Constraints for table `company`
--
ALTER TABLE `company`
  ADD CONSTRAINT `company_ibfk_1` FOREIGN KEY (`ParticipantID`) REFERENCES `transaction_participant` (`ParticipantID`) ON DELETE CASCADE;

--
-- Constraints for table `financial_analyst`
--
ALTER TABLE `financial_analyst`
  ADD CONSTRAINT `financial_analyst_ibfk_1` FOREIGN KEY (`StaffID`) REFERENCES `staff` (`StaffID`) ON DELETE CASCADE;

--
-- Constraints for table `fraud_alert`
--
ALTER TABLE `fraud_alert`
  ADD CONSTRAINT `fraud_alert_ibfk_1` FOREIGN KEY (`TransactionID`) REFERENCES `stock_transaction` (`TransactionID`) ON DELETE CASCADE;

--
-- Constraints for table `institution`
--
ALTER TABLE `institution`
  ADD CONSTRAINT `institution_ibfk_1` FOREIGN KEY (`ParticipantID`) REFERENCES `transaction_participant` (`ParticipantID`) ON DELETE CASCADE;

--
-- Constraints for table `investor`
--
ALTER TABLE `investor`
  ADD CONSTRAINT `investor_ibfk_1` FOREIGN KEY (`ParticipantID`) REFERENCES `transaction_participant` (`ParticipantID`) ON DELETE CASCADE;

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`StaffID`) REFERENCES `staff` (`StaffID`) ON DELETE CASCADE;

--
-- Constraints for table `manager`
--
ALTER TABLE `manager`
  ADD CONSTRAINT `manager_ibfk_1` FOREIGN KEY (`StaffID`) REFERENCES `staff` (`StaffID`) ON DELETE CASCADE;

--
-- Constraints for table `prediction`
--
ALTER TABLE `prediction`
  ADD CONSTRAINT `prediction_ibfk_1` FOREIGN KEY (`StockID`) REFERENCES `stock` (`StockID`) ON DELETE CASCADE;

--
-- Constraints for table `price_history`
--
ALTER TABLE `price_history`
  ADD CONSTRAINT `price_history_ibfk_1` FOREIGN KEY (`StockID`) REFERENCES `stock` (`StockID`) ON DELETE CASCADE;

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`ParticipantID`) REFERENCES `transaction_participant` (`ParticipantID`) ON DELETE SET NULL;

--
-- Constraints for table `stock_transaction`
--
ALTER TABLE `stock_transaction`
  ADD CONSTRAINT `stock_transaction_ibfk_1` FOREIGN KEY (`StockID`) REFERENCES `stock` (`StockID`),
  ADD CONSTRAINT `stock_transaction_ibfk_2` FOREIGN KEY (`ParticipantID`) REFERENCES `transaction_participant` (`ParticipantID`),
  ADD CONSTRAINT `stock_transaction_ibfk_3` FOREIGN KEY (`StaffID`) REFERENCES `staff` (`StaffID`);

--
-- Constraints for table `trades`
--
ALTER TABLE `trades`
  ADD CONSTRAINT `trades_ibfk_1` FOREIGN KEY (`BuyerID`) REFERENCES `transaction_participant` (`ParticipantID`),
  ADD CONSTRAINT `trades_ibfk_2` FOREIGN KEY (`SellerID`) REFERENCES `transaction_participant` (`ParticipantID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
