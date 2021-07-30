using System;
using System.Collections.Generic;
using System.Text;
using System.Net;
using System.Xml;
using System.Xml.Serialization;
using System.IO;
using System.Security.Cryptography;
using System.Collections;
using System.Drawing;
using System.Windows.Forms;
using Timekeeping.TimekeepingReqResp;

namespace Timekeeping
{
    public class TimeUtil
    {
        //////////////////////////////////////////////////////////////////////
        //
        // Create an md5 sum string of this string
        //
        //////////////////////////////////////////////////////////////////////
        static public string md5Sum(string str)
        {
            // First we need to convert the string into bytes, which
            // means using a text encoder.
            Encoder enc = System.Text.Encoding.UTF8.GetEncoder();

            // Create a buffer large enough to hold the string
            char[] chars = str.ToCharArray();
            byte[] utf8Text = new byte[enc.GetByteCount(chars, 0, chars.Length, true)];
            enc.GetBytes(chars, 0, chars.Length, utf8Text, 0, true);

            // Now that we have a byte array we can ask the CSP to hash it
            MD5 md5 = new MD5CryptoServiceProvider();
            byte[] result = md5.ComputeHash(utf8Text);

            // Build the final string by converting each byte
            // into hex and appending it to a StringBuilder
            StringBuilder sb = new StringBuilder();
            for (int i = 0; i < result.Length; i++)
            {
                sb.Append(result[i].ToString("x2"));
            }
            // And return it
            return sb.ToString();
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        static public string hashPassword(string username, string password)
        {
            return md5Sum(username + ":timekeeping:" + password);
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        static public string easyCrypt(string input)
        {
            int key = 129;
            StringBuilder inSb = new StringBuilder(input);
            StringBuilder outSb = new StringBuilder(input.Length);
            char c;
            for (int i = 0; i < input.Length; i++)
            {
                c = inSb[i];
                c = (char)(c ^ key);
                outSb.Append(c);
            }
            return outSb.ToString();
        }

    }

    class TimeRetrieval<N>
    {
        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        public N execute(NetworkCredential creds, string url)
        {
            string responseDoc = "";
            while (true)
            {
                try
                {
                    /*WebClient wc = new WebClient();
                    wc.Credentials = new NetworkCredential(username, password);
                    responseDoc = wc.DownloadString(url);
                    wc.Dispose();*/
                    WebFetcher wf = WebFetcher.createGet(creds, url);
                    responseDoc = wf.getString();
                    break;
                }
                catch (Exception e)
                {
                    throw new TimeCommunicationsError("GET", url, e);
                }
            }
            TextReader reader = new StringReader(responseDoc);
            XmlTextReader textReader = new XmlTextReader(reader);
            textReader.EntityHandling = EntityHandling.ExpandEntities;
            XmlSerializer responseSerializer = new XmlSerializer(typeof(N));
            N response = (N)responseSerializer.Deserialize(textReader);
            textReader.Close();
            reader.Close();
            reader.Dispose();
            return response;
        }
    }

    public class TimeCommunicationsError : ApplicationException
    {
        string m_url;
        string m_method;

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        public TimeCommunicationsError(string method, string url, Exception parent)
            : base(
            "Error communicating with server[" + method + " to " + url + "]: " + parent.Message, parent)
        {
            m_url = url;
            m_method = method;
        }

        public TimeCommunicationsError(string method, string url, string cause)
            : base(
            "Error communicating with server[" + method + " to " + url + "]: " + cause)
        {
            m_url = url;
            m_method = method;
        }
    }

    class TimeInteraction<M, N>
    {
        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        public N execute(NetworkCredential creds, string url, M request)
        {
            string requestDoc;
            using (TextWriter writer = new StringWriter())
            {
                XmlSerializer requestSerializer = new XmlSerializer(typeof(M));
                requestSerializer.Serialize(writer, request);
                requestDoc = writer.ToString();
                writer.Close();
                requestSerializer = null;
            }

            string responseDoc = "";
            while (true)
            {
                try
                {
                    /* WebClient wc = new WebClient();
                     wc.Credentials = new NetworkCredential(username, password);
                     responseDoc = wc.UploadString(url, requestDoc);
                     //wc.Dispose();
                     */
                    WebFetcher wf = WebFetcher.createPost(creds, url);
                    wf.writeData(requestDoc);
                    responseDoc = wf.getString();
                    break;
                }
                catch (Exception e)
                {
                    throw new TimeCommunicationsError("POST", url, e);
                }
            }

            N response;
            using (TextReader reader = new StringReader(responseDoc))
            {
                using (XmlTextReader textReader = new XmlTextReader(reader))
                {
                    textReader.EntityHandling = EntityHandling.ExpandEntities;
                    XmlSerializer responseSerializer = new XmlSerializer(typeof(N));
                    response = (N)responseSerializer.Deserialize(textReader);
                    textReader.Close();
                }
                reader.Close();
            }
            return response;
        }
    }

    public class XMLAPI
    {

        static string s_interactUrl;
        static NetworkCredential s_creds;

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        public static string InteractUrl
        {
            get { return s_interactUrl; }
            set { s_interactUrl = value; }
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        public static NetworkCredential Credentials
        {
            get { return s_creds; }
            set { s_creds = value; }
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        static public string validateConnection(string url, string username, string password)
        {
            WebFetcher wf = WebFetcher.createPost(new NetworkCredential(username, password), url + "?Validate=1");
            return wf.getString();
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        static public string validateConnection()
        {
            WebFetcher wf = WebFetcher.createPost(Credentials, InteractUrl + "?Validate=1");
            return wf.getString();
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        static public NOPResponse NOP(string adminName, string adminPassword)
        {
            TimeInteraction<NOPRequest, NOPResponse> interact = new TimeInteraction<NOPRequest, NOPResponse>();
            NOPRequest request = new NOPRequest();
            return interact.execute(Credentials, InteractUrl, request);
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        static public EnumerateTrackerUnitsResponse getTime(int userId, string sqlDate)
        {
            TimeInteraction<EnumerateTrackerUnitsRequest, EnumerateTrackerUnitsResponse> interact = new TimeInteraction<EnumerateTrackerUnitsRequest, EnumerateTrackerUnitsResponse>();
            EnumerateTrackerUnitsRequest request = new EnumerateTrackerUnitsRequest();
            request.userId = userId.ToString();
            request.Date = sqlDate.ToString();
            return interact.execute(Credentials, InteractUrl, request);
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        static public AuthenticateResponse authenticate(string username, string password)
        {
            TimeInteraction<AuthenticateRequest, AuthenticateResponse> interact = new TimeInteraction<AuthenticateRequest, AuthenticateResponse>();
            AuthenticateRequest request = new AuthenticateRequest();
            request.username = username;
            request.password = TimeUtil.hashPassword(username, password);
            return interact.execute(Credentials, InteractUrl, request);
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        static public int getUserID(string username)
        {
            TimeInteraction<UserIDRequest, UserIDResponse> interact = new TimeInteraction<UserIDRequest, UserIDResponse>();
            UserIDRequest request = new UserIDRequest();
            request.username = username;
            UserIDResponse response = interact.execute(Credentials, InteractUrl, request);
            return Int16.Parse(response.userId);
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        static public void submitTrackerUnit(int UserID, string Date, int Period, int ProjectID)
        {
            TimeInteraction<TrackerUnitSubmitRequest, TrackerUnitSubmitResponse> interact = new TimeInteraction<TrackerUnitSubmitRequest, TrackerUnitSubmitResponse>();
            TrackerUnitSubmitRequest request = new TrackerUnitSubmitRequest();
            request.UserID = "" + UserID;
            request.Date = Date;
            request.Period = "" + Period;
            request.ProjectID = "" + ProjectID;
            interact.execute(Credentials, InteractUrl, request);
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        static public void submitTrackerUnitRange(int UserID, string Date, int StartPeriod, int EndPeriod, int ProjectID)
        {
            TimeInteraction<TrackerUnitRangeSubmitRequest, TrackerUnitRangeSubmitResponse> interact = new TimeInteraction<TrackerUnitRangeSubmitRequest, TrackerUnitRangeSubmitResponse>();
            TrackerUnitRangeSubmitRequest request = new TrackerUnitRangeSubmitRequest();
            request.UserID = "" + UserID;
            request.Date = Date;
            request.StartPeriod = "" + StartPeriod;
            request.EndPeriod = "" + EndPeriod;
            request.ProjectID = "" + ProjectID;
            interact.execute(Credentials, InteractUrl, request);
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        static public EnumerateProjectsResponse getProjectCodes()
        {
            TimeInteraction<EnumerateProjectsRequest, EnumerateProjectsResponse> interact = new TimeInteraction<EnumerateProjectsRequest, EnumerateProjectsResponse>();
            EnumerateProjectsRequest request = new EnumerateProjectsRequest();
            return interact.execute(Credentials, InteractUrl, request);
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        static public void submitDay(int UserID, string Date)
        {
            TimeInteraction<SubmitDayRequest, SubmitDayResponse> interact = new TimeInteraction<SubmitDayRequest, SubmitDayResponse>();
            SubmitDayRequest request = new SubmitDayRequest();
            request.UserID = "" + UserID;
            request.Date = Date;
            interact.execute(Credentials, InteractUrl, request);
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        static public void retractDay(int UserID, string Date)
        {
            TimeInteraction<RetractDayRequest, RetractDayResponse> interact = new TimeInteraction<RetractDayRequest, RetractDayResponse>();
            RetractDayRequest request = new RetractDayRequest();
            request.UserID = "" + UserID;
            request.Date = Date;
            interact.execute(Credentials, InteractUrl, request);
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        static public bool isDaySubmitted(int UserID, string Date)
        {
            TimeInteraction<IsDaySubmittedRequest, IsDaySubmittedResponse> interact = new TimeInteraction<IsDaySubmittedRequest, IsDaySubmittedResponse>();
            IsDaySubmittedRequest request = new IsDaySubmittedRequest();
            request.UserID = "" + UserID;
            request.Date = Date;
            return ((IsDaySubmittedResponse)interact.execute(Credentials, InteractUrl, request)).Response;
        }
    }
}
