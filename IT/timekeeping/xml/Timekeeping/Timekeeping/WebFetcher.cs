//////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2007, Quantum Signal, LLC
// 
// This data and information is proprietary to, and a valuable trade 
// secret of, Quantum Signal, LLC.  It is given in confidence by Quantum 
// Signal, LLC. Its use, duplication, or disclosure is subject to the
// restrictions set forth in the License Agreement under which it has 
// been distributed.
//
//////////////////////////////////////////////////////////////////////
using System;
using System.Collections.Generic;
using System.Text;
using System.IO;
using System.Net;

namespace Timekeeping
{
    public class WebFetcher
    {
        HttpWebRequest m_webRequest;

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        public static WebFetcher createGet(NetworkCredential creds, string uri)
        {
            return new WebFetcher(creds, uri, "GET", null);
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        public static WebFetcher createPost(NetworkCredential creds, string uri)
        {
            return new WebFetcher(creds, uri, "POST", "application/x-www-form-urlencoded");
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        public void setCredentials(NetworkCredential nc)
        {
            m_webRequest.Credentials = nc;
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        public WebFetcher(NetworkCredential creds, string uri, string method, string type)
        {
            try
            {
                m_webRequest = (HttpWebRequest)HttpWebRequest.Create(uri);
                //m_webRequest.UnsafeAuthenticatedConnectionSharing = true;
                m_webRequest.KeepAlive = false;
                m_webRequest.Credentials = creds;
                m_webRequest.Method = method;
                m_webRequest.UserAgent = "TimeTrayClient";
                m_webRequest.PreAuthenticate = true;
               m_webRequest.ProtocolVersion = System.Net.HttpVersion.Version11;
                if (type != null)
                {
                    m_webRequest.ContentType = type;
                }
            }
            catch (Exception e)
            {
                throw e;
            }
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        public void writeData(string data)
        {
            try
            {
                byte[] bytedata = Encoding.UTF8.GetBytes(data);
                m_webRequest.ContentLength = bytedata.Length;

                using (Stream requestStream = m_webRequest.GetRequestStream())
                {
                    using (MemoryStream stream = new MemoryStream(bytedata))
                    {
                        stream.WriteTo(requestStream);
                    }
                    requestStream.Close();
                }
            }
            catch (Exception e)
            {
                throw e;
            }
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        public void writeFile(string filename)
        {
            try
            {
                using (FileStream fs = File.Open(filename, FileMode.Open, FileAccess.Read, FileShare.Read))
                {
                    fs.Seek(0, SeekOrigin.End);
                    long lSize = fs.Position;
                    fs.Seek(0, SeekOrigin.Begin);


           /*         //preauthenticate
                    HttpWebRequest WRequest = (HttpWebRequest)HttpWebRequest.Create(m_webRequest.RequestUri);
                    // Set the username and the password.
                    WRequest.Credentials = m_webRequest.Credentials;
                    WRequest.PreAuthenticate = true;
                    WRequest.UserAgent = m_webRequest.UserAgent;
                    WRequest.Method = "HEAD";
                    WRequest.Timeout = 10000;
                    HttpWebResponse WResponse = (HttpWebResponse)WRequest.GetResponse();
                    WResponse.Close();
                  */
                    if (lSize > 1024 * 1024 * 2)
                    {
                       m_webRequest.AllowWriteStreamBuffering = false;
                    }
                    m_webRequest.ContentLength = lSize;
                    //m_webRequest.SendChunked = true;

                    using (Stream requestStream = m_webRequest.GetRequestStream())
                    {
                        byte[] byteData;
                        if (lSize < 4096)
                        {
                            byteData = new byte[lSize];
                        }
                        else if (lSize < 65536)
                        {
                            byteData = new byte[4096];
                        }
                        else
                        {
                          byteData = new byte[65536];
                        }
                        int iRead = 0;
                        while ((iRead = fs.Read(byteData, 0, byteData.Length)) != 0)
                        {
                            requestStream.Write(byteData, 0, iRead);
                        }
                        requestStream.Close();
                    }
                    fs.Close();
                }
            }
            catch (Exception e)
            {
                throw e;
            }
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        public Stream getStream()
        {
            try
            {
                MemoryStream memStream = new MemoryStream();
                byte[] buffer = new byte[4096];
                using (HttpWebResponse httpWebResponse =
                        (HttpWebResponse)m_webRequest.GetResponse())
                {
                    using (Stream responseStream = httpWebResponse.GetResponseStream())
                    {
                        int iRead = 0;
                        while ((iRead = responseStream.Read(buffer, 0, buffer.Length)) != 0)
                        {
                            memStream.Write(buffer, 0, iRead);
                        }
                        responseStream.Close();
                    }
                    httpWebResponse.Close();
                }
                memStream.Seek(0, SeekOrigin.Begin);
                return memStream;
            }
            catch (Exception e)
            {
                throw e;
            }
        }

        //////////////////////////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////////////////////////////
        public string getString()
        {
            try
            {
                StringBuilder sb = new StringBuilder();
                using (HttpWebResponse httpWebResponse =
                        (HttpWebResponse)m_webRequest.GetResponse())
                {
                    using (Stream responseStream = httpWebResponse.GetResponseStream())
                    {
                        using (StreamReader reader =
                            new StreamReader(responseStream, Encoding.UTF8))
                        {
                            string line;
                            while ((line = reader.ReadLine()) != null)
                            {
                                sb.Append(line);
                            }
                        }
                        responseStream.Close();
                    }
                    httpWebResponse.Close();
                }
                return sb.ToString();
            }
            catch (Exception e)
            {
                throw e;
            }
        }
    }
}
