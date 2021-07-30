using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Text;
using System.Windows.Forms;
using Timekeeping.TimekeepingReqResp;

namespace Timekeeping
{
    public partial class Form1 : Form
    {
        public Form1()
        {
            InitializeComponent();
            XMLAPI.InteractUrl = "http://localhost:80/Timekeeping/API/Interact.php";
            XMLAPI.Credentials = new System.Net.NetworkCredential("cmshowers","123");
            XMLAPI.validateConnection();
            int UserID = XMLAPI.getUserID(XMLAPI.Credentials.UserName);
            string sqlDate = System.DateTime.Now.Year + "-" + System.DateTime.Now.Month + "-" + System.DateTime.Now.Day;
            EnumerateTrackerUnitsResponse units = (EnumerateTrackerUnitsResponse)XMLAPI.getTime(UserID, sqlDate);
            foreach (EnumerateTrackerUnitsResponseTrackerUnit unit in units.TrackerUnits)
            {
                listBox1.Items.Add(unit.Period.ToString());
            }
            EnumerateProjectsResponse codes = (EnumerateProjectsResponse)XMLAPI.getProjectCodes();
            foreach (EnumerateProjectsResponseProjectCode code in codes.ProjectCodes)
            {
                listBox2.Items.Add(code.ProjectCode + "(" + code.ProjectID + ")");
            }
            /* //Examples:
            XMLAPI.submitTrackerUnit(UserID, sqlDate, 5, 8);
            XMLAPI.submitTrackerUnitRange(UserID, sqlDate, 5, 10, 8);
            XMLAPI.submitDay(UserID, sqlDate);
            XMLAPI.retractDay(UserID, sqlDate);
            if (XMLAPI.isDaySubmitted(UserID, sqlDate))
                MessageBox.Show("Don't let them alter time!");*/
        }
    }
}